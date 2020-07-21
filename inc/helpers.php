<?php

use WPDev\Debug\Dumper;
use WPDev\Factories\PostFactory;
use WPDev\Models\Post;
use WPDev\Template\PluginTemplate;
use WPDev\Template\Template;

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
     * @return Post[] Array of WPDev\Models\Post objects or an empty array if there were no posts to map over.
     */
    function get_posts_from_query(WP_Query $wp_query): array
    {
    	return PostFactory::makeFromQuery($wp_query);
    }
}


if (! function_exists('template')) {
	/**
	 * Include a theme template file. Optionally pass data.
	 *
	 * @param string $file_name The file name of the template.
	 * @param array $data Data to be passed to view. Will also be extracted into variables.
	 *
	 * @return bool True if successfully included the template. Otherwise, false.
	 */
	function template(string $file_name, array $data = []): bool
    {
		return Template::render($file_name, $data);
	}
}

if (! function_exists('template_locate')) {
	/**
	 * Locates a template file.
	 *
	 * @param string $file_name
	 *
	 * @return string The path to the template file. Empty if none found.
	 */
	function template_locate(string $file_name): string
    {
		return Template::locate($file_name);
	}
}

if (! function_exists('plugin_template')) {
	/**
	 * Include a theme template file. Optionally pass data.
	 *
	 * @param string $file_path The file name of the template.
	 * @param array $data Data to be passed to view. Will also be extracted into variables.
	 *
	 * @return bool True if successfully included the template. Otherwise, false.
	 */
	function plugin_template(string $file_path, array $data = []): bool
    {
		return PluginTemplate::render($file_path, $data);
	}
}