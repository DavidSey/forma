<?php

namespace App\Service;

/**
 * Class BrokenSqlEscaper.
 */
class BrokenSqlEscaper
{
    /**
     * @param $value
     * @return mixed
     */
    public function escapeValue($value)
    {
        $escapedValue = mysqli_real_escape_string('test', $value);
        return $value;
    }
}
