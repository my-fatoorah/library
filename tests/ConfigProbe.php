<?php

namespace MyFatoorah\Test;

use MyFatoorah\Library\MyFatoorahHelper;

/**
 * Reaches the two protected methods that keep the countries configuration.
 *
 * Both take the file path as an argument, so a test can point them at a
 * temporary folder instead of src/.
 */
class ConfigProbe extends MyFatoorahHelper
{
    public static function update($mfConfigFile)
    {
        return self::updateMFConfigFile($mfConfigFile);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public static function report($reason, $mfConfigFile, $fileExists)
    {
        return self::reportConfigFailure($reason, $mfConfigFile, $fileExists);
    }
}
