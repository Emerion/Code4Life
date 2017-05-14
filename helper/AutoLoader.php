<?php

class AutoLoader
{
    /**
     * string[]
     */
    private $dirs = [];

    public function __construct()
    {
        spl_autoload_register([$this, 'autoLoad']);
    }

    /**
     * @param string $dir
     */
    public function registerDir($dir)
    {
        $this->dirs[] = $dir;
    }

    public function autoLoad($className)
    {
        $fileName = $className . '.php';
        foreach ($this->dirs as $dir) {
            if (file_exists($dir . $fileName)) {
                require_once $dir . $fileName;
            }
        }
    }

}
