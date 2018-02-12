<?php

namespace WPDev\Templates;

use Symfony\Component\Finder\Finder;

class Template
{
    protected $data;
    protected $fileName;
    protected $paths;
    protected $foundTemplates = [];

    /**
     * Constructor. Alternatively use `Template::include()` or `Template::locate()`.
     *
     * @param string $file_name
     * @param array $data
     */
    public function __construct(string $file_name, array $data = [])
    {
        $this->fileName       = basename($file_name);
        $this->data           = $data;
        $this->paths          = $this->buildValidPaths();
        $this->foundTemplates = $this->locateAllTemplates();
    }

    /**
     * For a more fluid syntax. Alternatively use `Template::include()` or `Template::locate()`.
     *
     * @param string $file_name
     * @param array $data
     *
     * @return $this
     */
    public static function create(string $file_name, array $data = [])
    {
        return new static($file_name, $data);
    }

    public function getTemplate()
    {
        if ( ! $this->foundTemplates) {
            return '';
        }

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        $file = reset($this->foundTemplates); // grab the first file

        return $file->getRealPath();
    }

    /**
     * Include a theme template file. Optionally pass data.
     *
     * @param string $file_name The file name of the template.
     * @param array $data Data to be passed to view. Will also be extracted into variables.
     *
     * @return bool True if successfully included the template. Otherwise, false.
     */
    public static function include(string $file_name, array $data = [])
    {
        return static::create($file_name, $data)->includeTemplate();
    }

    /**
     * Includes the template file passing along the data.
     *
     * @return bool True if successfully included the template. Otherwise, false.
     */
    public function includeTemplate()
    {
        if (!$template = $this->getTemplate()) {
            return false;
        }

        $data = $this->data;
        extract($data);
        include $this->getTemplate();

        return true;
    }

    /**
     * Locates a template file.
     *
     * @param string $file_name
     *
     * @return string The path to the template file. Empty if none found.
     */
    public static function locate(string $file_name)
    {
        return self::create($file_name)->getTemplate();
    }

    /*
    |--------------------------------------------------------------------------
    | Protected
    |--------------------------------------------------------------------------
    */

    protected function buildValidPaths()
    {
        $paths = array_unique($this->paths());

        $real_paths = array_filter($paths, function ($path) {
            return file_exists($path) && is_dir($path);
        });

        return $real_paths;
    }

    protected function locateAllTemplates()
    {
        dump($this->paths, $this->fileName);
        if (!$this->paths) {
            return [];
        }

        $finder    = new Finder();
        $templates = $finder->files()->in($this->paths)->name($this->fileName);

        return iterator_to_array($templates);
    }

    /**
     * Defines the paths to look for templates in.
     *
     * @return array
     */
    protected function paths()
    {
        return [
            get_stylesheet_directory().'/templates',
            get_template_directory().'/templates',
        ];
    }
}