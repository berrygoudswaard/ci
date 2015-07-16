<?php

namespace BerryGoudswaard\CI\Docker;

class Container
{
    private $cmd;
    private $envs;
    private $id;
    private $image;
    private $volumes = [];
    private $binds = [];
    private $log = '';
    private $callbacks = [];

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getCmd()
    {
        return $this->cmd;
    }

    public function setCmd($cmd)
    {
        $this->cmd = $cmd;
    }

    public function addVolume($destPath, $srcPath)
    {
        $this->volumes[$destPath] = new \stdClass();
    }

    public function addBind($srcPath, $destPath)
    {
        $this->binds[] = sprintf('%s:%s', $srcPath, $destPath);
    }

    public function setEnvs($envs = [])
    {
        $this->envs = $envs;
    }


    public function getOptions()
    {
        return [
            'Image' => $this->image,
            'Cmd' => $this->cmd,
            'Volumes' => $this->volumes,
            'Env' => $this->envs,
            'HostConfig' => [
                'Binds' => $this->binds
            ]
        ];

    }

    public function addToLog($data)
    {
        $this->log .= $data;

        foreach ($this->callbacks as $callback) {
            $callable = $callback['callable'];
            $arguments = $callback['arguments'];
            $arguments[] = $data;

            call_user_func_array($callable, $arguments);
        }
    }

    public function getLog()
    {
        return $this->log;
    }

    public function addCallback($callable, $arguments = [])
    {
        $this->callbacks[] = compact('callable', 'arguments');
    }
}
