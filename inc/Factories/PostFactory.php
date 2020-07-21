<?php
namespace WPDev\Factories;

use WPDev\Models\Post;

class PostFactory
{
	/**
	 * @param int|\WP_Post|null $wpPostIdOrObject
	 */
	public static function make($wpPostIdOrObject)
	{
		$post = get_post($wpPostIdOrObject);
		$postModelClass = apply_filters('wpdev.factory.postModelClass', Post::class, $post);

		return new $postModelClass($post);
	}

	public static function makeFromArray(array $idsOrPostObjects): array
    {
		return array_map([PostFactory::class, 'make'], $idsOrPostObjects);
	}

	/**
	 * @return Post[]
	 */
	public static function makeFromQuery(\WP_Query $wp_query): array
    {
		return self::makeFromArray($wp_query->posts);
	}

	public static function replacePostsInQuery(\WP_Query $wp_query): \WP_Query
	{
		$wp_query->posts = self::makeFromQuery($wp_query);
		return $wp_query;
	}
}