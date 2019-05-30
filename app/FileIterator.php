<?php

class FileIterator {

    public $files;
    public $iterator;

    function __construct() {
        $this->iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(TEMPLATE_DIR));
        $this->files = [];

        foreach ($this->iterator as $file) {
            if($file->isFile()) {
                array_push($this->files, substr($file->getPathname(), 2));
            }
        }
    }

    public function getFiles() {
        return $this->files;
    }

    public function copyAllCss() {
        foreach($this->files as $file) {
            if(strpos($file, '.css')) {
                copy($file, PREP_DIR . basename($file));
            }
        } 
    }

}