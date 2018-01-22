<?php
namespace WPDev;

use Brain\Hierarchy\Hierarchy;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class ControllerLoader {
    private $hierarchy;
    private $path;
    private $files;

    /**
     * @param \Brain\Hierarchy\Hierarchy $hierarchy
     */
    public function __construct(Hierarchy $hierarchy)
    {
        $this->hierarchy = $hierarchy;
        $this->path = get_theme_file_path().'/controllers';

        if (!file_exists($this->path)) {
            return;
        }

        $this->buildListOfFiles();

        foreach ($this->files as $filename => $file) {
            dump(pathinfo($filename, PATHINFO_FILENAME));
        }
    }

    protected function buildListOfFiles()
    {
        $DirectoryIterator = new RecursiveDirectoryIterator($this->path);
        $Iterator = new RecursiveIteratorIterator($DirectoryIterator);
        $Regex = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        $this->files = $Regex;
    }
}