<?php

namespace BerryGoudswaard\CI\Docker;

class DockerService
{
    private $restClient;

    public function __construct($restClient)
    {
        $this->restClient = $restClient;
    }

    protected function execute($method, $url, $options = [])
    {
        $options += [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ];

        $response = call_user_func_array(
            [$this->restClient, $method],
            [$url, $options]
        );

        return $response->json();
    }

    public function createImage($imageName)
    {
        $data = $this->restClient->post(
            sprintf('/images/create?fromImage=%s', $imageName)
        );

        return $data->getStatusCode() === 200;
    }

    public function createContainer(Container $container)
    {
        $data = $this->execute(
            'post',
            '/containers/create',
            ['json' => $container->getOptions()]
        );

        $container->setId($data['Id']);
        return $container;
    }

    public function runContainer(Container $container)
    {
        $data = $this->execute(
            'post',
            sprintf('/containers/%s/start', $container->getId())
        );

        $this->writeContainerLogs($container);
        return $container;
    }

    public function writeContainerLogs(Container $container)
    {
        $stream = $this->restClient->get(
            sprintf('/containers/%s/logs?stderr=1&stdout=1&follow=1', $container->getId()),
            ['stream' => true]
        );

        while (!$stream->getBody()->eof()) {
            $line = \GuzzleHttp\Stream\Utils::readLine($stream->getBody());
            $line = substr($line, 8);
            $container->addToLog($line);
        }
    }

    public function deleteContainer(Container $container)
    {
        $data = $this->execute(
            'delete',
            sprintf('containers/%s', $container->getId())
        );

        return $container;
    }

    public function getInfoForContainer(Container $container)
    {
        $data = $this->execute(
            'get',
            sprintf('containers/%s/json', $container->getId())
        );
        return $data;
    }
}
