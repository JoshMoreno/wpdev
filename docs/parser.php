<?php
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require_once __DIR__.'/vendor/autoload.php';

foreach ($loader->getClassMap() as $class => $path) {
    if (\Stringy\Stringy::create($class)->startsWith('WPDev\\Models')) {
        generate_yaml_file($class, __DIR__.'/_data/models/');
    }
}

foreach ($loader->getClassMap() as $class => $path) {
	if (\Stringy\Stringy::create($class)->startsWith('WPDev\\Facades')) {
		generate_yaml_file($class, __DIR__.'/_data/facades/');
	}
}