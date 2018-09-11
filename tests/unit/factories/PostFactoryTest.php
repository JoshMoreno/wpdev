<?php

namespace WPDev\Tests\Unit;

use Mockery;
use WP_Mock;
use WP_Mock\Tools\TestCase;
use WPDev\Factories\PostFactory;
use WPDev\Models\Post;

class PostFactoryTest extends TestCase
{
	public $mockPost;

	public function setUp()
	{
		parent::setUp();

		$this->mockPost     = Mockery::mock('WP_Post');
		$this->mockPost->ID = 1;

		WP_Mock::userFunction('get_post', [
			'return' => $this->mockPost,
		]);
	}

	public function testCanMakePost()
	{
		$this->assertInstanceOf(Post::class, PostFactory::make(1));
	}

	public function testCanMakeFromArray()
	{
		$this->assertContainsOnlyInstancesOf(
			Post::class,
			PostFactory::makeFromArray($this->mockPosts())
		);
	}

	public function testCanMakeFromQuery()
	{
		$this->assertContainsOnlyInstancesOf(
			Post::class,
			PostFactory::makeFromQuery($this->mockQuery())
		);
	}

	public function testCanReplacePostsInQuery()
	{
		$query = PostFactory::replacePostsInQuery($this->mockQuery());

		$this->assertInstanceOf(\WP_Query::class, $query);
		$this->assertContainsOnlyInstancesOf(Post::class, $query->posts);
	}

	public function testCanFilterToAnotherClass()
	{
		WP_Mock::onFilter('wpdev.factory.postModelClass')
		       ->with(Post::class)
		       ->reply(FakePostModel::class);

		$this->assertInstanceOf(FakePostModel::class, PostFactory::make(1));

		$this->assertContainsOnlyInstancesOf(
			FakePostModel::class,
			PostFactory::makeFromArray($this->mockPosts())
		);

		$this->assertContainsOnlyInstancesOf(
			FakePostModel::class,
			PostFactory::makeFromQuery($this->mockQuery())
		);

		$query = PostFactory::replacePostsInQuery($this->mockQuery());

		$this->assertContainsOnlyInstancesOf(FakePostModel::class, $query->posts);
	}

	public function mockPosts()
	{
		// mock posts
		$posts = [1,2,3];
		return array_map(function($id) {
			$post = Mockery::mock('WP_Post');
			$post->ID = $id;
			return $post;
		}, $posts);
	}

	public function mockQuery()
	{
		$query = Mockery::mock('WP_Query');
		$query->posts = $this->mockPosts();
		return $query;
	}
}

class FakePostModel extends Post {}