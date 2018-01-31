<?php

use WPDev\Debug\Dumper;

/*
|--------------------------------------------------------------------------
| Dump and Die
|--------------------------------------------------------------------------
*/
if (!function_exists('dd')) {
    function dd(...$args) {
        foreach ($args as $arg) {
            Dumper::dump($arg);
        }

        die();
    }
}