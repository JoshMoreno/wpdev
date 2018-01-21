<?php
/**
 * Plugin name: Custom Plugin
 * Description: Description goes here.
 * Version: 1.0.0
 * Author: Josh Moreno
 * Author URI: http://JoshMoreno.com
 */

use WPDev\CustomPostType;
use WPDev\OptionsPage;

require_once 'vendor/autoload.php';
require_once 'inc/bootstrap.php';

$projects = new CustomPostType('package');
$projects->register();


$optionsPage = new OptionsPage('Custom Settings');
$optionsPage->register();
