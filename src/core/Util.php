<?php
/**
 * This file is part of the Kayako-twitter package.
 *
 * Copyright (c) 2015 Mukesh Sharma <cogentmukesh@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kayako;

/**
 * Utilities class.
 */
class Util
{
    /**
     * Checks that a given string is a valid one.
     *
     * @param $string
     * @return bool
     */
    public static function isValidString($string)
    {
        return (strlen(trim($string)) > 0);
    }

    /**
     * Checks whether given array doesn't contain empty values`
     *
     * @param array $array
     * @return bool
     */
    public static function isValidArrayOfStrings($value)
    {
        if(is_array($value) === false || empty($value) === true) {
            return false;
        }

        foreach ($value as $item) {
            if (is_string($item) === false || strlen(trim($item)) === 0) {
                return false;
            }
        }

        return true;
    }
}
