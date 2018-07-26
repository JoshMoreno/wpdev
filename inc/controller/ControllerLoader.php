<?php

namespace WPDev\Controller;

use Brain\Hierarchy\Hierarchy;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class ControllerLoader
{
	protected $files;
	protected $hierarchy;
	/** @var ControllerInterface */
	protected $controller;
	protected $directories;
	protected $parentPath;
	protected $templateHierarchy;
	protected $lastDefinedClass;

	/**
	 * @param \Brain\Hierarchy\Hierarchy $hierarchy
	 */
	public function __construct(Hierarchy $hierarchy)
	{
		$this->hierarchy         = $hierarchy;
		$this->templateHierarchy = $hierarchy->getTemplates();
		$this->directories       = $this->getDirectories();
		$this->files             = apply_filters('wpdev.controllerFiles', $this->buildListOfFiles());

		$this->loadController();
	}

	public function buildData()
	{
		if ($this->controller) {
			return (array) $this->controller->build();
		}

		return [];
	}

	/**
	 * For more fluid syntax
	 *
	 * @param \Brain\Hierarchy\Hierarchy $hierarchy
	 *
	 * @return $this
	 */
	public static function create(Hierarchy $hierarchy)
	{
		return new static($hierarchy);
	}

	/**
	 * Returns the controller instance
	 *
	 * @return ControllerInterface
	 */
	public function getController()
	{
		return $this->controller;
	}

	/*
	|--------------------------------------------------------------------------
	| Protected
	|--------------------------------------------------------------------------
	*/
	protected function buildControllerKey($controllerPath)
	{
		foreach ($this->directories as $path) {
			$path           = rtrim($path, '/') . '/';
			$controllerPath = str_replace($path, '', $controllerPath);
		}

		return str_replace('.php', '', $controllerPath);
	}

	/**
	 * Gets all the controller files.
	 *
	 * @return array Filename => Path
	 */
	protected function buildListOfFiles()
	{
		if ( ! $this->directories) {
			return [];
		}

		$finder = new Finder();
		$files  = $finder->files()
		                 ->in($this->directories)
		                 ->name('*.php');

		$controller_files = [];
		$lastClass        = end(get_declared_classes());

		/** @var \SplFileInfo $file */
		foreach ($files as $file) {
			include_once $file->getRealPath();
			$currentClass = end(get_declared_classes());

			// last file loaded was not a class
			if ($currentClass === $lastClass) {
				continue;
			}

			$lastClass = $currentClass;

			if ($this->isValidController($currentClass)) {
				$key                    = $this->buildControllerKey($file->getRealPath());
				$controller_files[$key] = $currentClass;
			}
		}

		return $controller_files;
	}

	protected function getDirectories()
	{
		// account for both child and parent themes
		$directories = array_unique([
			get_stylesheet_directory() . '/controllers',
			get_template_directory() . '/controllers',
		]);

		// allow devs to mess with the paths
		$directories = (array) apply_filters('wpdev.controllerDirectories', $directories);

		return $this->onlyRealDirectories($directories);
	}

	protected function isValidController($className)
	{
		try {
			$controller = new ReflectionClass($className);
		} catch (\ReflectionException $e) {
			return false;
		}

		if ( ! $controller->implementsInterface('WPDev\Controller\ControllerInterface')) {
			return false;
		}

		if ( ! $controller->hasMethod('build')) {
			return false;
		}

		return true;
	}

	protected function loadController()
	{
		if ( ! $this->files) {
			return;
		}

		foreach ($this->templateHierarchy as $file_name) {
			if (empty($this->files[$file_name])) {
				continue;
			}

			$controller = $this->files[$file_name];
			$controller = new $controller();

			$this->controller = new $controller();
			break;
		}
	}

	protected function onlyRealDirectories(array $directories)
	{
		return array_filter($directories, function($directory)
		{
			return file_exists($directory) && is_dir($directory);
		});
	}
}
