<?php

namespace WPDev\Controller;

use Brain\Hierarchy\Hierarchy;
use Symfony\Component\Finder\Finder;

class ControllerLoader
{
    protected $files;
    protected $hierarchy;
    /** @var \WPDev\ControllerInterface */
    protected $controller;
    protected $paths;
    protected $parentPath;
    protected $templateHierarchy;

    /**
     * @param \Brain\Hierarchy\Hierarchy $hierarchy
     */
    public function __construct(Hierarchy $hierarchy)
    {
        $this->hierarchy         = $hierarchy;
        $this->templateHierarchy = $hierarchy->getTemplates();

        $this->paths = $this->buildPaths();

        $this->files = $this->buildListOfFiles();

        $this->files = apply_filters('wpdev.controllers', $this->files);

        $this->loadController();
    }

	/**
	 * For more fluid syntax
	 *
	 * @param \Brain\Hierarchy\Hierarchy $hierarchy
	 * @return $this
	 */
	public static function create(Hierarchy $hierarchy) {
		return new static($hierarchy);
    }

	/**
	 * Returns the controller instance
	 *
	 * @return \WPDev\ControllerInterface
	 */
	public function getController() {
		return $this->controller;
    }

    /**
     * Gets all the controller files.
     *
     * @return array Filename => Path
     */
    protected function buildListOfFiles()
    {
        if (!$this->paths) {
            return [];
        }

        $pattern = '/implements (\\\WPDev\\\)?ControllerInterface/';
        $finder  = new Finder();
        $files   = $finder->files()
                          ->in($this->paths)
                          ->name('*.php')
                          ->contains($pattern);

        $controller_files = [];

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            include_once $file->getRealPath();
            $key = $file->getBasename('.php');
            if ( ! isset($controller_files[$key])) {
                $controller_files[$key] = [];
            }
            $controller_files[$key][] = $file->getRealPath();
        }

        return $controller_files;
    }

    protected function buildPaths()
    {
        // account for both child and parent themes
        $paths = array_unique([
            get_stylesheet_directory().'/controllers',
            get_template_directory().'/controllers',
        ]);

        // allow devs to mess with the paths
        $paths = (array)apply_filters('wpdev.controllerpaths', $paths);

        // only dirs that actually exist
        $real_paths = array_filter($paths, function ($path) {
            return file_exists($path) && is_dir($path);
        });

        return $real_paths;
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

            $classname  = $this->getClassNameFromFile($this->files[$file_name][0]);
            $controller = new $classname();

            if (method_exists($controller, 'build')) {
                $this->controller = $controller;
                break; // only load one controller
            }
        }
    }

    public function buildData()
    {
        if ($this->controller) {
            return (array)$this->controller->build();
        }

        return [];
    }

    /**
     * Thanks to Jarret Byrne
     * Based off of @link http://jarretbyrne.com/2015/06/197/
     *
     * @param $path_to_file = string
     *
     * @return mixed|string
     */
    protected function getClassNameFromFile($path_to_file)
    {
        //Grab the contents of the file
        $contents = file_get_contents($path_to_file);

        //Start with a blank namespace and class
        $namespace = '';
        $class     = '';

        //Set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $getting_namespace = false;
        $getting_class     = false;

        //Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {

            //If this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $getting_namespace = true;
            }

            //If this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && $token[0] == T_CLASS) {
                $getting_class = true;
            }

            //While we're grabbing the namespace name...
            if ($getting_namespace === true) {

                //If the token is a string or the namespace separator...
                if (is_array($token) && in_array($token[0], [
                        T_STRING,
                        T_NS_SEPARATOR,
                    ])) {

                    //Append the token's value to the name of the namespace
                    $namespace .= $token[1];
                } elseif ($token === ';') {

                    //If the token is the semicolon, then we're done with the namespace declaration
                    $getting_namespace = false;
                }
            }

            //While we're grabbing the class name...
            if ($getting_class === true) {

                //If the token is a string, it's the name of the class
                if (is_array($token) && $token[0] == T_STRING) {

                    //Store the token's value as the class name
                    $class = $token[1];

                    //Got what we need, stope here
                    break;
                }
            }
        }

        //Build the fully-qualified class name and return it
        return $namespace ? $namespace.'\\'.$class : $class;
    }
}
