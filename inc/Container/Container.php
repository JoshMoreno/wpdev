<?php

namespace WPDev\Container;

class Container implements \ArrayAccess
{
	public $data = [];

	public function add($key, $value)
	{
		$this->data[$key] = $value;

		return $this;
	}

	public function get($key, $default = null)
	{
		if ($this->has($key)) {
			return $this->data[$key];
		}

		return $default;
	}

	public function has($key)
	{
		return isset($this->data[$key]);
	}

	public function remove($key)
	{
		unset($this->data[$key]);

		return $this;
	}

	public function toArray()
	{
		return $this->data;
	}

	/*
	|--------------------------------------------------------------------------
	| \ArrayAccess Implementation
	|--------------------------------------------------------------------------
	*/
	/**
	 * Whether key exists
	 */
	public function offsetExists($key)
	{
		return $this->has($key);
	}

	/**
	 * Key retrieve
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}

	/**
	 * Key/value to set
	 */
	public function offsetSet($key, $value)
	{
		return $this->add($key, $value);
	}

	/**
	 * Key to unset
	 */
	public function offsetUnset($key){
        return $this->remove($key);
	}
}