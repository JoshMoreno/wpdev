<?php

use WPDev\Debug\Dumper;
use WPDev\Models\Post;

/*
|--------------------------------------------------------------------------
| Dump and Die
|--------------------------------------------------------------------------
*/
if ( ! function_exists('dd')) {
    function dd(...$args)
    {
        foreach ($args as $arg) {
            Dumper::dump($arg);
        }

        die();
    }
}

/**
 * Map WP_Query->posts to WPDev\Models\Post[]
 *
 * @param $wp_query
 *
 * @return array
 */
if ( ! function_exists('get_posts_from_query')) {
    function get_posts_from_query($wp_query)
    {
        if ( ! $wp_query instanceof WP_Query) {
            return [];
        }

        return array_map(function ($post) {
            return new Post($post);
        }, $wp_query->posts);
    }
}
