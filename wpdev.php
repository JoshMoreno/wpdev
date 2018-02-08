<?php
/**
 * Plugin name: WPDev
 * Description: Framework to streamline and normalize WordPress development.
 * Version: 1.0.0
 * Author: Josh Moreno
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/bootstrap.php';

function wpdev_main_plugin_file_name() {
	return __FILE__;
}