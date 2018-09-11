<?php
namespace WPDev\Factories;

use Webmozart\Assert\Assert;
use WPDev\Models\Post;

class PostFactory
{
	/**
	 * @param $wpPostIdOrObject
	 *
	 * @return Post
	 */
	public static function make($wpPostIdOrObject)
	{
		$post = get_post($wpPostIdOrObject);
		$postModelClass = apply_filters('wpdev.factory.postModelClass', Post::class, $post);

		return new $postModelClass($post);
	}

	/**
	 * @param $idsOrPostObjects
	 */
	public static function makeFromArray($idsOrPostObjects)
	{
		Assert::isArray($idsOrPostObjects);
		return array_map([PostFactory::class, 'make'], $idsOrPostObjects);
	}

	/**
	 * @param \WP_Query $wp_query
	 *
	 * @return Post[]
	 */
	public static function makeFromQuery(\WP_Query $wp_query)
	{
		return self::makeFromArray($wp_query->posts);
	}

	/**
	 * @param \WP_Query $wp_query
	 *
	 * @return \WP_Query
	 */
	public static function replacePostsInQuery(\WP_Query $wp_query)
	{
		$wp_query->posts = self::makeFromQuery($wp_query);
		return $wp_query;
	}
}