<?php

use WPDev\Debug\Dumper;
use WPDev\Models\Post;

if ( ! function_exists('dd')) {
    /**
     * Dump and die
     *
     * @param mixed ...$args Items to dump
     */
    function dd(...$args)
    {
        foreach ($args as $arg) {
            Dumper::dump($arg);
        }

        die();
    }
}

if ( ! function_exists('get_posts_from_query')) {
    /**
     * Map WP_Query->posts to WPDev\Models\Post objects
     *
     * @param \WP_Query $wp_query A WP_Query object
     *
     * @return array If not empty will be an array of WPDev\Models\Post objects
     */
    function get_posts_from_query(WP_Query $wp_query)
    {
        return array_map(function ($post) {
            return new Post($post);
        }, $wp_query->posts);
    }
}
