<?php
//namespace WPDev\Tests\Integration;
//
//use WP_UnitTestCase;

use WPDev\Factories\PostFactory;

class PostFactoryTest extends WP_UnitTestCase
{
	/**
	 * @var WP_Post
	 */
	public $post;

	public function setUp()
	{
		parent::setUp();
		$this->post = $this->factory()->post->create_and_get();
	}

	public function testMakesPostFromId()
	{
		$post = PostFactory::make($this->post->ID);
		$this->assertInstanceOf(\WPDev\Models\Post::class, $post);
		$this->assertEquals($this->post->ID, $post->id());
	}

	public function testMakesPostFromObject()
	{
		$post = PostFactory::make($this->post);
		$this->assertInstanceOf(\WPDev\Models\Post::class, $post);
		$this->assertEquals($this->post->ID, $post->id());
	}

	public function testCanFilterPostModelClass()
	{
		add_filter('wpdev.factory.postModelClass', function() {
			return FakePostModel::class;
		});

		$post = PostFactory::make($this->post);
		$this->assertInstanceOf(FakePostModel::class, $post);
	}

	public function testFactoryPassesPostObjectToFilters()
	{
		add_filter('wpdev.factory.postModelClass', function($postModelClass, $post) {
			$this->assertInstanceOf(WP_Post::class, $post);
			return $post;
		}, 10, 2);

		$post = PostFactory::make($this->post);
	}
}

class FakePostModel extends \WPDev\Models\Post
{

}