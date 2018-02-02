<?php

namespace WPDev\Controller;

interface ControllerInterface
{
	/**
	 * This is the data that will get exposed to the view.
	 * It will be extracted into variables - extract().
	 *
	 * @return array
	 */
	public function build(): array;
}