<?php
/**
 * Plugin name: WPDev
 * Description: Framework to streamline and normalize WordPress development.
 * Version: 1.0.0
 * Author: Josh Moreno
 */

// we may have installed this plugin via composer
if (!class_exists(\WPDev\Models\Post::class)) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// can't move this to composer.json autoloader because
// it breaks tests
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/bootstrap.php';

function wpdev_main_plugin_file_name(): string
{
	return __FILE__;
}