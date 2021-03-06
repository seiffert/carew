<?php

namespace Carew\Command;

use Carew\Document;
use Carew\Processor;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends BaseCommand
{
    private $container;

    public function __construct(\Pimple $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('carew:build')
            ->setDescription('Builds static html files from markdown source')
            ->setDefinition(array(
                new InputOption('--web-dir', null, InputOption::VALUE_REQUIRED, 'Where to write generated content', getcwd().'/web'),
            ))
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $startAt = microtime(true);
        $memoryUsage = memory_get_usage();

        $this->container['base_dir'] = $baseDir = realpath($input->getOption('base-dir'));
        if (false === $baseDir) {
            throw new \InvalidArgumentException('Could not find base dir path');
        }

        $this->container['web_dir'] = $webDir = $input->getOption('web-dir');
        if (!is_dir($webDir)) {
            $this->container['filesystem']->mkdir($webDir);
        }

        $processor = $this->container['processor'];

        $input->getOption('verbose') and $output->writeln('Processing <comment>Posts</comment>');
        $posts = $processor->process($baseDir.'/posts', '*-*-*-*.md', Document::TYPE_POST);
        $posts = $processor->sortByDate($posts);

        $input->getOption('verbose') and $output->writeln('Processing <comment>Pages</comment>');
        $pages = $processor->process($baseDir.'/pages', '*.md', Document::TYPE_PAGE);

        $input->getOption('verbose') and $output->writeln('Processing <comment>Api</comment>');
        $api = $processor->process($baseDir.'/api', '*', Document::TYPE_API, true);

        $documents = array_merge($posts, $pages, $api);

        $tags       = $processor->buildCollection($documents, 'tags');
        $navigation = $processor->buildCollection($documents, 'navigation');

        $input->getOption('verbose') and $output->writeln('Processing <comment>Tags page</comment>');
        $documents = array_merge($documents, $tags = $processor->processTags($tags, $baseDir));

        $input->getOption('verbose') and $output->writeln('Processing <comment>Index page</comment>');
        $documents = array_merge($documents, $processor->processIndex($pages, $posts, $baseDir));

        $input->getOption('verbose') and $output->writeln('<comment>Cleaned target folder</comment>');
        $this->container['filesystem']->remove($this->container['finder']->in($webDir)->exclude(basename(realpath($baseDir))));

        $twigGlobales = array(
            'latest'     => reset($posts),
            'navigation' => $navigation,
            'documents'  => $documents,
            'posts'      => $posts,
            'tags'       => $tags,
        );
        foreach ($twigGlobales as $key => $global) {
            $this->container['twig']->addGlobal($key, $global);
        }

        $builder = $this->container['builder'];
        foreach ($documents as $document) {
            $input->getOption('verbose') and $output->writeln(sprintf('Render <comment>%s</comment>', $document->getPath()));
            $builder->buildDocument($document);
        }

        $input->getOption('verbose') and $output->writeln('<comment>Copy assets</comment>');
        foreach ($this->container['themes'] as $theme) {
            $path = $theme.'/assets/';
            if (is_dir($path)) {
                $this->container['filesystem']->mirror($path, $webDir.'/', null, array('override' => true));
            }
        }

        $output->writeln('<info>Build finished</info>');
        $input->getOption('verbose') and $output->writeln(sprintf('Time: %.2f seconds, Memory: %.2fMb', (microtime(true) - $startAt), (memory_get_usage() - $memoryUsage)/(1024 * 1024)));
    }
}
