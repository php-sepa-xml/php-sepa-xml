<?php

if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
    throw new \LogicException('Could not find autoload.php in vendor/');
}

require $autoloadFile;

define('XML_DIR', __DIR__.'/fixtures/xml/');
