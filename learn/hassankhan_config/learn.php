<?php
use Noodlehaus\Config;

require dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR .'autoload.php';

$config_path = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
$config = new Config(
                        [
                            $config_path . 'config_dev.php',
                            $config_path . 'config_pro.php'
                        ]
                    );
var_dump($config->all());die();
