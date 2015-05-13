<?php

namespace BerryGoudswaard\CI\Configuration;

use BerryGoudswaard\CI\Configuration\Script;
use Symfony\Component\Yaml\Parser;

class Config
{
    protected $language;
    protected $tags = [];
    protected $script;
    protected $workingDir;

    public function __construct($config)
    {
        $this->processConfig($config);
    }

    public function processConfig($config)
    {
        $config += [
            'databases' => [],
            'before_script' => [],
            'script' => [],
            'after_script' => []
        ];

        if (!($workingDir = $config['workingDir'])) {
            throw new \Exception('No working dir provided');
        }

        if (!($images = $config['images'])) {
            throw new \Exception('No images provided');
        }

        $script = new Script();
        $script->setDatabaseScripts($config['databases']);
        $script->setBeforeScripts($config['before_script']);
        $script->setScripts($config['script']);
        $script->setAfterScripts($config['after_script']);
        if (!empty($config['ciScript']) && ($ciScript = $config['ciScript'])) {
            $ciScript->setTargetFile($ciScript);
        }

        $this->setWorkingDir($workingDir);
        $this->setImages($images);
        $this->setScript($script);
    }

    public function setScript(Script $script)
    {
        $this->script = $script;
    }

    public function getScript()
    {
        return $this->script;
    }

    public function setImages($images)
    {
        $this->images = $images;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function getWorkingDir()
    {
        return $this->workingDir;
    }

    public function setWorkingDir($workingDir)
    {
        $this->workingDir = $workingDir;
    }
}
