<?php

namespace Carew;

use Symfony\Component\Finder\SplFileInfo;

class Document
{
    const TYPE_POST    = 'post';
    const TYPE_PAGE    = 'page';
    const TYPE_API     = 'api';
    const TYPE_UNKNOWN = 'unknown';

    private $body;
    private $file;
    private $filePath;
    private $layout;
    private $metadatas;
    private $path;
    private $title;
    private $toc;
    private $type;
    private $vars;

    public function __construct(SplFileInfo $file = null, $filePath = null, $type = self::TYPE_UNKNOWN)
    {
        $this->body      = '';
        $this->file      = $file;
        $this->filePath  = $filePath;
        $this->layout    = 'default';
        $this->metadatas = array('tags' => array(), 'navigation' => array());
        $this->path      = $file ? $file->getBaseName() : '.';
        $this->title     = $file ? $file->getBaseName() : '.';
        $this->toc       = array();
        $this->type      = $type;
        $this->vars      = array();
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    public function getMetadatas()
    {
        return $this->metadatas;
    }

    public function setMetadatas($metadatas, $merge = true)
    {
        if ($merge) {
            $this->metadatas = array_replace_recursive($this->metadatas, $metadatas);
        } else {
            $this->metadatas = $metadatas;

        }

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getRootPath()
    {
        $path = ltrim($this->path, '/');
        if (0 === $nb = substr_count($path, DIRECTORY_SEPARATOR)) {
            return '.';
        }

        return rtrim(str_repeat('../', $nb), '/');
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getToc()
    {
        return $this->toc;
    }

    public function setToc($toc)
    {
        $this->toc = $toc;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getVars()
    {
        return $this->vars;
    }

    public function setVars($vars)
    {
        $this->vars = $vars;

        return $this;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }
}
