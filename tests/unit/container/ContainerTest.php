<?php

namespace WPDev\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPDev\Container\Container;

class ContainerTest extends TestCase
{
	/**
	 * @var \WPDev\Container\Container
	 */
	protected $container;
	/*
	|--------------------------------------------------------------------------
	| Setup and Teardown
	|--------------------------------------------------------------------------
	*/
	public function setUp(): void
	{
		parent::setUp();
		$this->container = new Container();
	}

	public function tearDown(): void
	{
		$this->container = null;
	}

	public function testCanAddToContainer()
	{
		$this->container->add('something', 'someValue');

		$this->assertEquals('someValue', $this->container->data['something']);
	}

	public function testCanCheckIfContainerHasKey()
	{
		$this->container->add('something', 'someValue');

		$this->assertEquals(true, $this->container->has('something'));
	}

	public function testCanGetFromContainer()
	{
		$this->container->add('something', 'someValue');

		$this->assertEquals('someValue', $this->container->get('something'));
	}

	public function testCanReturnDefaultValueIfNotSet()
	{
		$val = $this->container->get('keyThatDoesNotExist', 123);
		$this->assertEquals(123, $val);
	}

	public function testCanRemoveFromContainer()
	{
		$this->container->add('something', 'someValue');

		$this->container->remove('something');

		$this->assertFalse($this->container->has('something'));
	}

	public function testConvertsToArray()
	{
		$this->container->data = [
			'test' => 'something',
			'another' => 123,
			'last' => ['red', 'green', 'blue'],
		];

		$this->assertEquals($this->container->data, $this->container->toArray());
	}

	/*
	|--------------------------------------------------------------------------
	| Array Access
	|--------------------------------------------------------------------------
	*/
	public function testArrayAccessCanSet()
	{
		$this->assertFalse($this->container->has('something'));

		$this->container['something'] = 'value';

		$this->assertTrue($this->container->has('something'));
	}

	public function testArrayAccessCanCheck()
	{
		$this->assertFalse(isset($this->container['something']));

		$this->container['something'] = 'value';

		$this->assertTrue(isset($this->container['something']));
	}

	public function testArrayAccessCanGet()
	{
		$this->assertNull($this->container['something']);

		$this->container['something'] = 'value';

		$this->assertEquals('value', $this->container['something']);
	}

	public function testArrayAccessCanRemove()
	{
		$this->container['something'] = 'value';

		$this->assertEquals('value', $this->container['something']);

		unset($this->container['something']);

		$this->assertNull($this->container['something']);
	}

}