<?php

namespace MyFatoorah\Library;

use Exception;

class MyFatoorahHelper
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * The file name or the logger object
     * It is used in logging the payment/shipping events to help in debugging and monitor the process and connections.
     *
     * @var string|object
     */
    public static $loggerObj;

    /**
     * The function name that will be used in the debugging if $loggerObj is set as a logger object.
     *
     * @var string
     */
    public static $loggerFunc;

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Returns the country code and the phone after applying MyFatoorah restriction
     *
     * Matching regular expression pattern: ^(?:(\+)|(00)|(\\*)|())[0-9]{3,14}((\\#)|())$
     * if (!preg_match('/^(?:(\+)|(00)|(\\*)|())[0-9]{3,14}((\\#)|())$/iD', $inputString))
     * String length: inclusive between 0 and 11
     *
     * @param string $inputString It is the input phone number provide by the end user.
     *
     * @return array That contains the phone code in the 1st element the the phone number the the 2nd element.
     *
     * @throws Exception    Throw exception if the input length is less than 3 chars or long than 14 chars.
     */
    public static function getPhone($inputString)
    {
        // Remove any arabic digit
        $string3 = self::convertArabicDigitstoEnglish($inputString);

        // Keep Only digits
        $string4 = preg_replace('/[^0-9]/', '', $string3);

        // Remove 00 at start
        if (strpos($string4, '00') === 0) {
            $string4 = substr($string4, 2);
        }

        if (!$string4) {
            return ['', ''];
        }

        // Check for the allowed length
        $len = strlen($string4);
        if ($len < 3 || $len > 14) {
            throw new Exception('Phone Number length must be between 3 to 14 digits');
        }

        // Get the phone arr
        if (strlen(substr($string4, 3)) > 3) {
            return [
                substr($string4, 0, 3),
                substr($string4, 3)
            ];
        }

        return [
            '',
            $string4
        ];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Converts any Arabic or Persian numbers to English digits
     *
     * @param string $inputString It is the input phone number provide by the end user.
     *
     * @return string
     */
    protected static function convertArabicDigitstoEnglish($inputString)
    {
        $newNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $persianDecimal = ['&#1776;', '&#1777;', '&#1778;', '&#1779;', '&#1780;', '&#1781;', '&#1782;', '&#1783;', '&#1784;', '&#1785;']; // 1. Persian HTML decimal
        $arabicDecimal  = ['&#1632;', '&#1633;', '&#1634;', '&#1635;', '&#1636;', '&#1637;', '&#1638;', '&#1639;', '&#1640;', '&#1641;']; // 2. Arabic HTML decimal
        $arabic         = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩']; // 3. Arabic Numeric
        $persian        = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']; // 4. Persian Numeric

        $string0 = str_replace($persianDecimal, $newNumbers, $inputString);
        $string1 = str_replace($arabicDecimal, $newNumbers, $string0);
        $string2 = str_replace($arabic, $newNumbers, $string1);

        return str_replace($persian, $newNumbers, $string2);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the rate that will convert the given weight unit to MyFatoorah default weight unit.
     * Weight must be in kg, g, lbs, or oz. Default is kg.
     *
     * @param string $unit It is the weight unit used.
     *
     * @return float|int The conversion rate that will convert the given unit into the kg.
     *
     * @throws Exception Throw exception if the input unit is not support.
     */
    public static function getWeightRate($unit)
    {
        $lUnit = strtolower($unit);

        // 'kg' is the default
        $rateUnits = [
            '1'         => ['kg', 'kgs', 'كج', 'كلغ', 'كيلو جرام', 'كيلو غرام'],
            '0.001'     => ['g', 'جرام', 'غرام', 'جم'],
            '0.453592'  => ['lbs', 'lb', 'رطل', 'باوند'],
            '0.0283495' => ['oz', 'اوقية', 'أوقية'],
        ];

        foreach ($rateUnits as $rate => $unitArr) {
            if (array_search($lUnit, $unitArr) !== false) {
                return (float) $rate;
            }
        }
        throw new Exception('Weight units must be in kg, g, lbs, or oz. Default is kg');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the rate that will convert the given dimension unit to MyFatoorah default dimension unit.
     * Dimension must be in cm, m, mm, in, or yd. Default is cm.
     *
     * @param string $unit It is the dimension unit used in width, hight, or depth.
     *
     * @return float|int The conversion rate that will convert the given unit into the cm.
     *
     * @throws Exception        Throw exception if the input unit is not support.
     */
    public static function getDimensionRate($unit)
    {
        $lUnit = strtolower($unit);

        // 'cm' is the default
        $rateUnits = [
            '1'     => ['cm', 'سم'],
            '100'   => ['m', 'متر', 'م'],
            '0.1'   => ['mm', 'مم'],
            '2.54'  => ['in', 'انش', 'إنش', 'بوصه', 'بوصة'],
            '91.44' => ['yd', 'يارده', 'ياردة'],
        ];

        foreach ($rateUnits as $rate => $unitArr) {
            if (array_search($lUnit, $unitArr) !== false) {
                return (float) $rate;
            }
        }
        throw new Exception('Dimension units must be in cm, m, mm, in, or yd. Default is cm');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Validate Webhook version 1 signature function
     * keep it for the old system
     *
     * @param array  $dataModel Webhook request array
     * @param string $secretKey Webhook secret key
     * @param string $signature MyFatoorah signature
     * @param int    $eventType MyFatoorah Event type Number (1, 2, 3 , 4)
     *
     * @return bool
     */
    public static function isSignatureValid($dataModel, $secretKey, $signature, $eventType = 1)
    {
        if ($eventType == 2) {
            unset($dataModel['GatewayReference']);
        }

        uksort($dataModel, 'strcasecmp');

        return self::checkSignatureValidation($dataModel, $secretKey, $signature);
    }

    /**
     * Checks whether the provided signature is correct or not for MyFatoorah Webhook
     *
     * @param array  $dataModel Webhook request array
     * @param string $secretKey Webhook secret key
     * @param string $signature MyFatoorah signature
     *
     * @return bool
     */
    protected static function checkSignatureValidation($dataModel, $secretKey, $signature)
    {
        $mapFun = function ($v, $k) {
            return sprintf('%s=%s', $k, $v);
        };

        $outputArr = array_map($mapFun, $dataModel, array_keys($dataModel));
        $output    = implode(',', $outputArr);

        // Generate hash of $field string
        $hash = base64_encode(hash_hmac('sha256', $output, $secretKey, true));

        return hash_equals($hash, $signature);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get a list of MyFatoorah countries, their API URLs, and names.
     *
     * @return array of MyFatoorah data
     *
     * @throws Exception
     */
    public static function getMFCountries()
    {
        $mfConfigFile = __DIR__ . '/mf-config.json';

        // A missing file reports no time, which reads as very old - and that is what it is.
        if (time() - (int) @filemtime($mfConfigFile) > 3600) {
            self::updateMFConfigFile($mfConfigFile);
        }

        $content = file_get_contents($mfConfigFile);
        return ($content) ? (array) json_decode($content, true) : [];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Update the mf-config.json file
     *
     * @param string $mfConfigFile The file name used in caching data.
     *
     * @return bool True when the file now holds a fresh configuration.
     *
     * @throws Exception
     */
    protected static function updateMFConfigFile($mfConfigFile)
    {
        // An existing file is overwritten in place, a missing one is created in its folder.
        $fileExists = file_exists($mfConfigFile);
        $target     = $fileExists ? $mfConfigFile : dirname($mfConfigFile);

        if (!is_writable($target)) {
            return self::reportConfigFailure('no permission to write ' . $target, $mfConfigFile, $fileExists);
        }

        // The cache can be written, so ask the portal for the latest configuration.
        $curl = curl_init('https://portal.myfatoorah.com/Files/API/mf-config.json');

        $option = [
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3, // Maximum time to establish the connection (in seconds)
            CURLOPT_TIMEOUT        => 6  // Maximum time allowed for the entire request (in seconds)
        ];
        curl_setopt_array($curl, $option);

        $response  = curl_exec($curl);
        $httpCode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);

        unset($curl);

        // Record the attempt, so a portal that is down is not asked again before an hour.
        if ($fileExists) {
            touch($mfConfigFile);
        }

        if ($httpCode == 200 && is_string($response)) {
            // Remove the hidden character between the single quotes
            $responseText = trim($response, '﻿');
            $countries    = json_decode($responseText, true);

            // Only overwrite the cache with something that is actually the configuration.
            if (is_array($countries) && !empty($countries)) {
                return file_put_contents($mfConfigFile, $responseText) !== false;
            }

            // The portal answered, but not with the configuration.
            return self::reportConfigFailure('the portal did not answer with the configuration', $mfConfigFile, $fileExists);
        }

        return self::reportConfigFailure('the portal could not be read, ' . ($curlError ?: 'HTTP ' . $httpCode), $mfConfigFile, $fileExists);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Report a failed configuration update, and stop when there is no file to fall back on.
     *
     * @param string $reason       Why the configuration could not be refreshed.
     * @param string $mfConfigFile The file name used in caching data.
     * @param bool   $fileExists   Whether a usable copy is already on disk.
     *
     * @return bool Always false, when the file on disk keeps the vendor working.
     *
     * @throws Exception
     */
    protected static function reportConfigFailure($reason, $mfConfigFile, $fileExists)
    {
        // The log keeps the reason even where the caller swallows the exception below.
        error_log('MyFatoorah: ' . $reason . '.');

        // The file on disk keeps the vendor working, so the log is enough.
        if ($fileExists) {
            return false;
        }

        // Nothing is cached, so no payment can be made. Say why here instead of failing with an empty list later.
        throw new Exception('MyFatoorah could not read its countries configuration: ' . $reason
            . '. There is no copy in ' . $mfConfigFile . ' either, so no payment can be processed.'
            . ' Restore the mf-config.json file that ships with the library, or disable MyFatoorah'
            . ' until the portal can be reached.');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Filter an input from global variables like $_GET, $_POST, $_REQUEST, $_COOKIE, $_SERVER
     *
     * @param string $name The field name the need to be filter.
     * @param string $type The input type to be filter (GET, POST, REQUEST, COOKIE, SERVER).
     *
     * @return string|null
     */
    public static function filterInputField($name, $type = 'GET')
    {
        if (isset($GLOBALS["_$type"][$name])) {
            return htmlspecialchars($GLOBALS["_$type"][$name], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        return null;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the payment status link
     *
     * @param string $url       The payment URL link
     * @param string $paymentId The payment Id
     *
     * @return string
     */
    public static function getPaymentStatusLink($url, $paymentId)
    {
        // To overcome session urls
        $pattern = '/MpgsAuthentication.*|ApplePayComplete.*|GooglePayComplete.*/i';
        return preg_replace($pattern, "Result?paymentId=$paymentId", $url);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
