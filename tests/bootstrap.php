<?php

if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
    throw new \LogicException('Could not find autoload.php in vendor/');
}

require $autoloadFile;

define('XSD_DIR', __DIR__.'/fixtures/xsd/');
define('XML_DIR', __DIR__.'/fixtures/xml/');
