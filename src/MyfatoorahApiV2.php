<?php

namespace MyFatoorah\Library;

use Exception;

/**
 * Class MyfatoorahApiV2 is responsible for handling calling MyFatoorah API endpoints. Also, It has necessary library functions that help in providing the correct parameters used endpoints.
 *
 * MyFatoorah offers a seamless business experience by offering a technology put together by our tech team. This enables smooth business operations involving sales activity, product invoicing, shipping, and payment processing. MyFatoorah invoicing and payment gateway solution trigger your business to greater success at all levels in the new age world of commerce. Leverage your sales and payments at all e-commerce platforms (ERPs, CRMs, CMSs) with transparent and slick applications that are well-integrated into social media and telecom services. For every closing sale click, you make a business function gets done for you, along with generating factual reports and statistics to fine-tune your business plan with no-barrier low-cost.
 * Our technology experts have designed the best GCC E-commerce solutions for the native financial instruments (Debit Cards, Credit Cards, etc.) supporting online sales and payments, for events, shopping, mall, and associated services.
 *
 * Created by MyFatoorah http://www.myfatoorah.com/
 * Developed By tech@myfatoorah.com
 * Date: 03/03/2021
 * Time: 12:00
 *
 * API Documentation on https://myfatoorah.readme.io/docs
 * Library Documentation and Download link on https://myfatoorah.readme.io/docs/php-library
 * 
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright 2021 MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyfatoorahApiV2 {
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * The URL used to connect to MyFatoorah test/live API server
     * 
     * @var string 
     */
    protected $apiURL = '';

    /**
     * The API Token Key is the authentication which identify a user that is using the app
     * To generate one follow instruction here https://myfatoorah.readme.io/docs/live-token
     *  
     * @var string
     */
    protected $apiKey;

    /**
     * This is the file name or the logger object
     * It will be used in logging the payment/shipping events to help in debugging and monitor the process and connections.
     * 
     * @var string|object
     */
    protected $loggerObj;

    /**
     * If $loggerObj is set as a logger object, you should set this var with the function name that will be used in the debugging.
     * 
     * @var string 
     */
    protected $loggerFunc;

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Constructor
     * Initiate new MyFatoorah API process
     *  
     * @param string        $apiKey      The API Token Key is the authentication which identify a user that is using the app. To generate one follow instruction here https://myfatoorah.readme.io/docs/live-token.
     * @param string        $countryMode Select the country mode.
     * @param boolean       $isTest      If This set to true, the process will be on the test mode. Set it to false for live mode.
     * @param string|object $loggerObj   It is optional. This is the file name or the logger object. It will be used in logging the payment/shipping events to help in debugging and monitor the process and connections. Leave it null, if you done't want to log the events.
     * @param string        $loggerFunc  It is optional. If $loggerObj is set as a logger object, you should set this var with the function name that will be used in the debugging.
     */
    public function __construct($apiKey, $countryMode = 'KWT', $isTest = false, $loggerObj = null, $loggerFunc = null) {

        $mfCountries = $this->getMyFatoorahCountries();

        $code = strtoupper($countryMode);
        if (isset($mfCountries[$code])) {
            $this->apiURL = ($isTest) ? $mfCountries[$code]['testv2'] : $mfCountries[$code]['v2'];
        } else {
            $this->apiURL = ($isTest) ? 'https://apitest.myfatoorah.com' : 'https://api.myfatoorah.com';
        }

        $this->apiKey     = trim($apiKey);
        $this->loggerObj  = $loggerObj;
        $this->loggerFunc = $loggerFunc;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param string         $url        It is the MyFatoorah API endpoint URL
     * @param array          $postFields It is the array of the POST request parameters. It should be set to null if the request is GET.
     * @param integer|string $orderId    It is optional. It is the order id or the payment id of the process. It will be used in the events logging.
     * @param string         $function   It is optional. The function name that made the request. It will be used in the events logging.
     * 
     * @return object           The response object as the result of a successful calling to the API.
     * 
     * @throws Exception        Throw exception if there is any curl error or a validation error in the MyFatoorah API endpoint URL
     */
    public function callAPI($url, $postFields = null, $orderId = null, $function = null) {

        //to prevent json_encode adding lots of decimal digits
        ini_set('precision', 14);
        ini_set('serialize_precision', -1);

        $request = isset($postFields) ? 'POST' : 'GET';
        $fields  = json_encode($postFields);

        $msgLog = "Order #$orderId ----- $function";

        if ($function != 'Direct Payment') {
            $this->log("$msgLog - Request: $fields");
        }

        //***************************************
        //call url
        //***************************************
        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST  => $request,
            CURLOPT_POSTFIELDS     => $fields,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer $this->apiKey", 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true
        ));

        $res = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        //example set a local ip to host apitest.myfatoorah.com
        if ($err) {
            $this->log("$msgLog - cURL Error: $err");
            throw new Exception($err);
        }

        $this->log("$msgLog - Response: $res");

        $json = json_decode($res);

        //***************************************
        //check for errors
        //***************************************

        $error = $this->getAPIError($json, $res);
        if ($error) {
            $this->log("$msgLog - Error: $error");
            throw new Exception($error);
        }

        //***************************************
        //Success 
        //***************************************
        return $json;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Handles Endpoint Errors Function
     * 
     * @param object|string $json
     * @param string        $res
     * 
     * @return string
     */
    protected function getAPIError($json, $res) {

        if (isset($json->IsSuccess) && $json->IsSuccess == true) {
            return '';
        }

        //to avoid blocked IP <html><head><title>403 Forbidden</title></head><body><center><h1>403 Forbidden</h1></center><hr><center>Microsoft-Azure-Application-Gateway/v2</center></body></html>
        $stripHtmlStr = strip_tags($res);
        if ($res != $stripHtmlStr) {
            return trim(preg_replace('/\s+/', ' ', $stripHtmlStr));
        }

        //Check for the errors
        $err = $this->getJsonErrors($json);
        if ($err) {
            return $err;
        }

        if (!$json) {
            return (!empty($res) ? $res : 'Kindly, review your MyFatoorah admin configuration due to a wrong entry.');
        }

        if (is_string($json)) {
            return $json;
        }

        return '';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Check for the json (response model) errors
     * 
     * @param object|string $json
     * 
     * @return string
     */
    protected function getJsonErrors($json) {

        if (isset($json->ValidationErrors) || isset($json->FieldsErrors)) {
            //$err = implode(', ', array_column($json->ValidationErrors, 'Error'));

            $errorsObj = isset($json->ValidationErrors) ? $json->ValidationErrors : $json->FieldsErrors;
            $blogDatas = array_column($errorsObj, 'Error', 'Name');

            return implode(', ', array_map(function ($k, $v) {
                        return "$k: $v";
                    }, array_keys($blogDatas), array_values($blogDatas)));
        }

        if (isset($json->Data->ErrorMessage)) {
            return $json->Data->ErrorMessage;
        }

        //if not get the message. this is due that sometimes errors with ValidationErrors has Error value null so either get the "Name" key or get the "Message"
        //example {"IsSuccess":false,"Message":"Invalid data","ValidationErrors":[{"Name":"invoiceCreate.InvoiceItems","Error":""}],"Data":null}
        //example {"Message":"No HTTP resource was found that matches the request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'.","MessageDetail":"No route providing a controller name was found to match request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'"}
        if (isset($json->Message)) {
            return $json->Message;
        }

        return '';
    }

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
     * @return array        That contains the phone code in the 1st element the the phone number the the 2nd element.
     * 
     * @throws Exception    Throw exception if the input length is less than 3 chars or long than 14 chars.
     */
    public static function getPhone($inputString) {

        //remove any arabic digit
        $newNumbers = range(0, 9);

        $persianDecimal = ['&#1776;', '&#1777;', '&#1778;', '&#1779;', '&#1780;', '&#1781;', '&#1782;', '&#1783;', '&#1784;', '&#1785;']; // 1. Persian HTML decimal
        $arabicDecimal  = ['&#1632;', '&#1633;', '&#1634;', '&#1635;', '&#1636;', '&#1637;', '&#1638;', '&#1639;', '&#1640;', '&#1641;']; // 2. Arabic HTML decimal
        $arabic         = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩']; // 3. Arabic Numeric
        $persian        = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']; // 4. Persian Numeric

        $string0 = str_replace($persianDecimal, $newNumbers, $inputString);
        $string1 = str_replace($arabicDecimal, $newNumbers, $string0);
        $string2 = str_replace($arabic, $newNumbers, $string1);
        $string3 = str_replace($persian, $newNumbers, $string2);

        //Keep Only digits
        $string4 = preg_replace('/[^0-9]/', '', $string3);

        //remove 00 at start
        if (strpos($string4, '00') === 0) {
            $string4 = substr($string4, 2);
        }

        if (!$string4) {
            return ['', ''];
        }

        //check for the allowed length
        $len = strlen($string4);
        if ($len < 3 || $len > 14) {
            throw new Exception('Phone Number lenght must be between 3 to 14 digits');
        }

        //get the phone arr
        if (strlen(substr($string4, 3)) > 3) {
            return [
                substr($string4, 0, 3),
                substr($string4, 3)
            ];
        } else {
            return [
                '',
                $string4
            ];
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * It will log the payment/shipping process events
     * 
     * @param string $msg It is the string message that will be written in the log file
     * 
     * @return null
     */
    public function log($msg) {

        if (!$this->loggerObj) {
            return;
        }
        if (is_string($this->loggerObj)) {
            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - ' . $msg, 3, $this->loggerObj);
        } else if (method_exists($this->loggerObj, $this->loggerFunc)) {
            $this->loggerObj->{$this->loggerFunc}($msg);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the rate that will convert the given weight unit to MyFatoorah default weight unit.
     * 
     * @param string $unit It is the weight unit used. Weight must be in kg, g, lbs, or oz. Default is kg.
     * 
     * @return double|integer The conversion rate that will convert the given unit into the kg. 
     * 
     * @throws Exception Throw exception if the input unit is not support. Weight must be in kg, g, lbs, or oz. Default is kg.
     */
    public static function getWeightRate($unit) {

        $unit1 = strtolower($unit);
        if ($unit1 == 'kg' || $unit1 == 'kgs' || $unit1 == 'كج' || $unit1 == 'كلغ' || $unit1 == 'كيلو جرام' || $unit1 == 'كيلو غرام') {
            $rate = 1; //kg is the default
        } else if ($unit1 == 'g' || $unit1 == 'جرام' || $unit1 == 'غرام' || $unit1 == 'جم') {
            $rate = 0.001;
        } else if ($unit1 == 'lbs' || $unit1 == 'lb' || $unit1 == 'رطل' || $unit1 == 'باوند') {
            $rate = 0.453592;
        } else if ($unit1 == 'oz' || $unit1 == 'اوقية' || $unit1 == 'أوقية') {
            $rate = 0.0283495;
        } else {
            throw new Exception('Weight units must be in kg, g, lbs, or oz. Default is kg');
        }

        return $rate;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the rate that will convert the given dimension unit to MyFatoorah default dimension unit.
     * 
     * @param string $unit It is the dimension unit used in width, hight, or depth. Dimension must be in cm, m, mm, in, or yd. Default is cm.
     * 
     * @return double|integer         The conversion rate that will convert the given unit into the cm.
     * 
     * @throws Exception    Throw exception if the input unit is not support. Dimension must be in cm, m, mm, in, or yd. Default is cm.
     */
    public static function getDimensionRate($unit) {

        $unit1 = strtolower($unit);
        if ($unit1 == 'cm' || $unit1 == 'سم') {
            $rate = 1; //cm is the default
        } elseif ($unit1 == 'm' || $unit1 == 'متر' || $unit1 == 'م') {
            $rate = 100;
        } else if ($unit1 == 'mm' || $unit1 == 'مم') {
            $rate = 0.1;
        } else if ($unit1 == 'in' || $unit1 == 'انش' || $unit1 == 'إنش' || $unit1 == 'بوصه' || $unit1 == 'بوصة') {
            $rate = 2.54;
        } else if ($unit1 == 'yd' || $unit1 == 'يارده' || $unit1 == 'ياردة') {
            $rate = 91.44;
        } else {
            throw new Exception('Dimension units must be in cm, m, mm, in, or yd. Default is cm');
        }

        return $rate;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the rate that will convert the given currency to the default currency of MyFatoorah portal account.
     * 
     * @param string $currency The currency that will be converted into the currency of MyFatoorah portal account.
     * 
     * @return string       The conversion rate that will convert the given currency into the default currency of MyFatoorah portal account.
     * 
     * @throws Exception    Throw exception if the input currency is not support by MyFatoorah portal account.
     */
    public function getCurrencyRate($currency) {

        $json = $this->getCurrencyRates();
        foreach ($json as $value) {
            if ($value->Text == $currency) {
                return $value->Value;
            }
        }
        throw new Exception('The selected currency is not supported by MyFatoorah');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get list of MyFatoorah currency rates
     * 
     * @return object
     */
    public function getCurrencyRates() {

        $url = "$this->apiURL/v2/GetCurrenciesExchangeList";
        return $this->callAPI($url, null, null, 'Get Currencies Exchange List');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Calculate the amount value that will be paid in each gateway
     * 
     * @param double|integer $totalAmount
     * @param string         $currency
     * @param string         $paymentCurrencyIso
     * @param object         $allRatesData
     * 
     * @return array
     */
    protected function calcGatewayData($totalAmount, $currency, $paymentCurrencyIso, $allRatesData) {

        //if ($currency != $paymentCurrencyIso) {
        foreach ($allRatesData as $data) {
            if ($data->Text == $currency) {
                $baseCurrencyRate = $data->Value;
            }
            if ($data->Text == $paymentCurrencyIso) {
                $gatewayCurrencyRate = $data->Value;
            }
        }

        if (isset($baseCurrencyRate) && isset($gatewayCurrencyRate)) {

            $baseAmount = ceil(((int) ($totalAmount * 1000)) / $baseCurrencyRate / 10) / 100;

            return [
                'GatewayTotalAmount' => round(($baseAmount * $gatewayCurrencyRate), 3),
                'GatewayCurrency'    => $paymentCurrencyIso
            ];
        } else {
            return [
                'GatewayTotalAmount' => $totalAmount,
                'GatewayCurrency'    => $currency
            ];
        }

        //        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Validate webhook signature function
     * 
     * @param array  $dataArray webhook request array
     * @param string $secret    webhook secret key
     * @param string $signature MyFatoorah signature
     * @param int    $eventType
     * 
     * @return boolean
     */
    public static function isSignatureValid($dataArray, $secret, $signature, $eventType = 0) {

        if ($eventType == 2) {
            unset($dataArray['GatewayReference']);
        }

        uksort($dataArray, 'strcasecmp');

        // uksort($data, function ($a, $b) {
        //   $a = mb_strtolower($a);
        //   $b = mb_strtolower($b);
        //   return strcmp($a, $b);
        // });

        $output = implode(',', array_map(
                        function ($v, $k) {
                    return sprintf("%s=%s", $k, $v);
                },
                        $dataArray,
                        array_keys($dataArray)
        ));

        //        $data      = utf8_encode($output);
        //        $keySecret = utf8_encode($secret);
        // generate hash of $field string 
        $hash = base64_encode(hash_hmac('sha256', $output, $secret, true));

        if ($signature === $hash) {
            return true;
        } else {
            return false;
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get a list of MyFatoorah countries and their API URLs and names
     * 
     * @return array of MyFatoorah data
     */
    public static function getMyFatoorahCountries() {

        $cachedFile = dirname(__FILE__) . '/mf-config.json';

        if (file_exists($cachedFile)) {
            if ((time() - filemtime($cachedFile) > 3600)) {
                $countries = self::createNewMFConfigFile($cachedFile);
            }

            if (!empty($countries)) {
                return $countries;
            }

            $cache = file_get_contents($cachedFile);
            return ($cache) ? json_decode($cache, true) : [];
        } else {
            return self::createNewMFConfigFile($cachedFile);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * 
     * @param string $cachedFile
     * 
     * @return array
     */
    static protected function createNewMFConfigFile($cachedFile) {

        $curl = curl_init('https://portal.myfatoorah.com/Files/API/mf-config.json');
        curl_setopt_array($curl, array(
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true
        ));

        $response  = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code == 200) {
            file_put_contents($cachedFile, $response);
            return json_decode($response, true);
        } else if ($http_code == 403) {
            touch($cachedFile);
            $fileContent = file_get_contents($cachedFile);
            if (!empty($fileContent)) {
                return json_decode($fileContent, true);
            }
        }
        return [];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
