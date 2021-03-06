<?php

namespace BerryGoudswaard\CI\Command\Jobs;

use BerryGoudswaard\CI\Docker\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('jobs:run')
            ->setDescription('Run a job based on a config file')
            ->addArgument(
                'config',
                InputArgument::REQUIRED,
                'Path to the project'
            )
            ->addOption(
               'env',
               null,
               InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
               'Additional env values',
               []
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getArgument('config');

        $terminalDimensions = $this->getApplication()->getTerminalDimensions();
        $container = $this->getApplication()->getContainer();
        $configurationService = $container->get('configurationService');
        $dockerService = $container->get('dockerService');

        $configuration = $configurationService->createFromYaml($configFile, $input->getoption('env'));
        $script = $configuration->getScript();

        $allSuccess = true;
        foreach ($configuration->getImages() as $image) {
            $output->writeln(sprintf('<info>%s</info>', str_repeat('=', $terminalDimensions[0])));
            $output->writeln(sprintf('<info>Running %s</info>', $image));
            $output->writeln(sprintf('<info>%s</info>', str_repeat('=', $terminalDimensions[0])));

            $container = new Container();
            $container->setImage($image);

            foreach ($configuration->getVolumes() as $volume) {
                $container->addBind($volume['source'], $volume['target']);
            }
            $container->addVolume($script->getTargetFile(), $script->getTmpFile());
            $container->addBind($configuration->getWorkingDir(), '/home/ci/project');
            $container->addBind($script->getTmpFile(), $script->getTargetFile());
            $container->addCallback([$this, 'logListener'], [$output]);

            $output->writeln('<comment>Updating image</comment>');
            if (!$dockerService->createImage($container->getImage())) {
                throw new \Exception('Image could not be updated/pulled');
            }

            $output->writeln('<comment>Creating container</comment>');
            if (!$container = $dockerService->createContainer($container)) {
                throw new \Exception('Container could not be created');
            }

            $output->writeln('<comment>Running container</comment>');
            if (!$container = $dockerService->runContainer($container)) {
                throw new \Exception('Container could not be started');
            }

            $result = $dockerService->getInfoForContainer($container);

            if ($allSuccess && $result['State']['ExitCode'] !== 0) {
                $allSuccess = false;
            }

            $output->writeln('<comment>Deleting container</comment>');
            if (!$container = $dockerService->deleteContainer($container)) {
                throw new \Exception('Container could not be deleted');
            }
        }

        $script->deleteTmpFile();
        return (int)!$allSuccess;
    }

    public function logListener($output, $data)
    {
        $output->write($data);
    }
}
