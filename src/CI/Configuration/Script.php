<?php

namespace BerryGoudswaard\CI\Configuration;

class Script
{
    private $databaseScripts = [];
    private $envs = [];
    private $beforeScripts = [];
    private $scripts = [];
    private $afterScripts = [];
    private $tmpFile;
    private $targetFile = '/tmp/ci.sh';

    public function setDatabaseScripts(array $databaseConfigs)
    {
        foreach ($databaseConfigs as $driver => $databases) {
            foreach ($databases as $database) {
                $this->databaseScripts[] = sprintf(
                    'mysql -u root -e "CREATE DATABASE IF NOT EXISTS %s;"',
                    $database['database']
                );

                $this->databaseScripts[] = sprintf(
                    'mysql -u root -e "CREATE USER \'%s\'@\'localhost\' IDENTIFIED BY \'%s\';"',
                    $database['username'],
                    $database['password']
                );

                $this->databaseScripts[] = sprintf(
                    'mysql -u root -e "GRANT ALL ON %s.* TO \'%s\'@\'localhost\';"',
                    $database['database'],
                    $database['username']
                );
            }
        }
    }

    public function setEnvs(array $envs)
    {
        foreach ($envs as $env) {
            $this->envs[] = sprintf('export %s', $env);
        }
    }

    public function setBeforeScripts(array $scripts)
    {
        $this->beforeScripts = $scripts;
    }

    public function setScripts(array $scripts)
    {
        $this->scripts = $scripts;
    }

    public function setAfterScripts(array $scripts)
    {
        $this->afterScripts = $scripts;
    }

    public function getTargetFile()
    {
        return $this->targetFile;
    }

    public function setTargetFile($targetFile)
    {
        $this->targetFile = $targetFile;
    }

    public function getTmpFile()
    {
        if (!isset($this->tmpFile)) {
            $lines = array_merge(
                ['#!/bin/bash'],
                [implode(' && ', $this->envs)],
                [implode(' && ', $this->databaseScripts)],
                [implode(' && ', $this->beforeScripts)],
                [implode(' && ', $this->scripts)],
                [implode(' && ', $this->afterScripts)]
            );

            $script = implode(PHP_EOL, $lines);

            // Create the script
            $this->tmpFile = tempnam(sys_get_temp_dir(), 'ci');
            file_put_contents($this->tmpFile, $script);
            chmod($this->tmpFile, 0777);
        }

        return $this->tmpFile;
    }

    public function deleteTmpFile()
    {
        if (($tmpFile = $this->getTmpFile()) && file_exists($tmpFile)) {
            unlink($tmpFile);
            unset($tmpFile);
        }
    }
}
