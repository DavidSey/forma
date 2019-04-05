<?php
/**
 * Created by PhpStorm.
 * User: davids
 * Date: 19/03/2019
 * Time: 14:44
 */

namespace App\Service;

class Antispam
{
    public function isSpam($text)
    {
        return strlen($text) < 50;
    }
}
