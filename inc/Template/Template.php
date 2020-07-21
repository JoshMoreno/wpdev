<?php

namespace WPDev\Template;

use Symfony\Component\Finder\Finder;
use Webmozart\Assert\Assert;

class Template
{
    protected $data;
    protected $fileName;
    protected $paths;
    protected $foundTemplates = [];

    /**
     * Alternatively use `Template::render()` or `Template::locate()`.
     */
    public function __construct(string $file_name, array $data = [])
    {
        $this->fileName       = basename($file_name);
        $this->data           = $data;
        $this->paths          = $this->buildValidPaths();
        $this->foundTemplates = $this->locateAllTemplates();
    }

    public static function create(string $file_name, array $data = []): self
    {
        return new static($file_name, $data);
    }

    public function getTemplate(): string
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
    public static function render(string $file_name, array $data = []): bool
    {
        return static::create($file_name, $data)->includeTemplate();
    }

    /**
     * Includes the template file passing along the data.
     *
     * @return bool True if successfully included the template. Otherwise, false.
     */
    public function includeTemplate(): bool
    {
        if (!$template = $this->getTemplate()) {
            return false;
        }

        $data = $this->data;
        extract($data, EXTR_OVERWRITE);
        include $template;

        return true;
    }

    /**
     * Locates a template file.
     */
    public static function locate(string $file_name): string
    {
        return self::create($file_name)->getTemplate();
    }

    protected function buildValidPaths(): array
    {
        $paths = array_unique($this->paths());

        return array_filter($paths, function ($path) {
            return file_exists($path) && is_dir($path);
        });
    }

    protected function excludedPaths(): array
    {
        return [
            'plugins'
        ];
    }

    protected function locateAllTemplates(): array
    {
        if (!$this->paths) {
            return [];
        }

        $finder    = new Finder();
        $templates = $finder->files()->in($this->paths)->exclude($this->excludedPaths())->name($this->fileName);

        return iterator_to_array($templates);
    }

    /**
     * Defines the paths to look for templates in.
     */
    protected function paths(): array
    {
        return [
            get_stylesheet_directory().'/templates',
            get_template_directory().'/templates',
        ];
    }
}