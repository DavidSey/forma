<?php

namespace App\Service;

/**
 * Class ConfigFileLoader.
 */
class ConfigFileLoader
{
    /**
     * @param string $filename
     * @return bool|string
     */
    public function load(string $filename)
    {
        return file_get_contents($filename);
    }
}
