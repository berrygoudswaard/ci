<?php

namespace BerryGoudswaard\CI\Console;

use BerryGoudswaard\CI\Command;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Application extends BaseApplication
{
    protected $container;

    public function __construct()
    {
        return parent::__construct('CI', '0.0.1');
    }

    public function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\Jobs\RunCommand();
        return $commands;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer(ContainerBuilder $container)
    {
        $this->container = $container;
    }
}
