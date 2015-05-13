<?php

$autoloadFiles = array(
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../../../vendor/autoload.php',
);

foreach ($autoloadFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}
