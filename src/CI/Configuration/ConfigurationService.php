<?php

namespace BerryGoudswaard\CI\Configuration;

use BerryGoudswaard\CI\Configuration\Config;
use Symfony\Component\Yaml\Parser;

class ConfigurationService
{
    public function createFromYaml($file, $envs = [])
    {
        $yaml = new Parser();
        $configData = $yaml->parse(file_get_contents($file));
        $configData['workingDir'] = dirname($file);
        return new Config($configData, $envs);
    }
}
