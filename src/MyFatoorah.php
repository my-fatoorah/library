<?php

namespace MyFatoorah\Library;

use Exception;

/**
 * The class the rest of the library is built on: MyFatoorahList, MyFatoorahRefund,
 * MyFatoorahShipping, MyFatoorahSupplier, MyFatoorahPayment, MyFatoorahWebhook,
 * MyFatoorahPayments and MyFatoorahSessions all extend it.
 *
 * It holds callAPI(), through which every request to a MyFatoorah endpoint goes:
 * it sends the request, logs it, reads the response and turns an API error into
 * an exception. Everything above it only builds the request and reads Data back.
 *
 * The notice below is the second of two. The daily update rewrites this file from a
 * copy with its comments stripped, so it does not survive an update - but a copy
 * modified far enough to be worth keeping has the update turned off, and then this is
 * where the notice stays. The other is at the top of autoload.php, which the update
 * never rewrites. Keeping either one satisfies the licence.
 *
 * API documentation     https://myfatoorah.readme.io/docs
 * Library documentation https://myfatoorah.readme.io/docs/php-library
 *
 * @author    MyFatoorah Plugin Team <tech@myfatoorah.com>
 * @author    Rasha Saeed <rsaeed@myfatoorah.com>
 * @copyright Copyright (c) 2026 MyFatoorah
 * @license   MIT https://opensource.org/licenses/MIT
 */
class MyFatoorah extends MyFatoorahHelper
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * The configuration used to connect to MyFatoorah test/live API server
     *
     * @var array
     */
    protected $config = [];

    /**
     * The URL used to connect to MyFatoorah test/live API server
     *
     * @var string
     */
    protected $apiURL = '';

    /**
     * The MyFatoorah PHP Library version
     *
     * @var string
     */
    protected $version = '2.2';

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Constructor that initiates a new MyFatoorah API process
     *
     * @param array $config It has the required keys (apiKey, isTest, and vcCode) to process a MyFatoorah API request.
     */
    public function __construct($config)
    {
        $mfCountries = self::getMFCountries();

        $this->setApiKey($config);
        $this->setIsTest($config);
        $this->setVcCode($config, $mfCountries);

        $this->config['loggerObj']  = empty($config['loggerObj']) ? null : $config['loggerObj'];
        $this->config['loggerFunc'] = empty($config['loggerFunc']) ? null : $config['loggerFunc'];

        // To use logger as static
        self::$loggerObj  = $this->config['loggerObj'];
        self::$loggerFunc = $this->config['loggerFunc'];

        $code         = $this->config['vcCode'];
        $this->apiURL = $this->config['isTest'] ? $mfCountries[$code]['testv2'] : $mfCountries[$code]['v2'];
    }

    /**
     * Get the API URL
     * The URL used to connect to MyFatoorah test/live API server
     *
     * @return string
     */
    public function getApiURL()
    {
        return $this->apiURL;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Set the API token Key
     * The API Token Key is the authentication which identify a user that is using the app.
     * To generate one follow instruction here https://myfatoorah.readme.io/docs/live-token
     *
     * @param array $config It has the required keys (apiKey, isTest, and vcCode) to process a MyFatoorah API request.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setApiKey($config)
    {
        if (empty($config['apiKey'])) {
            throw new Exception('Config array must have the "apiKey" key.');
        }

        $config['apiKey'] = trim($config['apiKey']);
        if (empty($config['apiKey'])) {
            throw new Exception('The "apiKey" key is required and must be a string.');
        }

        $this->config['apiKey'] = $config['apiKey'];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Set the test mode. Set it to false for live mode
     *
     * @param array $config It has the required keys (apiKey, isTest, and vcCode) to process a MyFatoorah API request.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setIsTest($config)
    {
        if (!isset($config['isTest'])) {
            throw new Exception('Config array must have the "isTest" key.');
        }

        if (!is_bool($config['isTest'])) {
            throw new Exception('The "isTest" key must be boolean.');
        }

        $this->config['isTest'] = $config['isTest'];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Set the vendor country code of the MyFatoorah account
     *
     * @param array $config      It has the required keys (apiKey, isTest, and vcCode) to process a MyFatoorah API request.
     * @param array $mfCountries The MyFatoorah countries, their API URLs, and names.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setVcCode($config, $mfCountries)
    {
        $config['vcCode'] = $config['vcCode'] ?? $config['countryCode'] ?? '';
        if (empty($config['vcCode'])) {
            throw new Exception('Config array must have the "vcCode" key.');
        }

        $countriesCodes = array_keys($mfCountries);

        $config['vcCode'] = strtoupper($config['vcCode']);
        if (!in_array($config['vcCode'], $countriesCodes)) {
            throw new Exception('The "vcCode" key must be one of (' . implode(', ', $countriesCodes) . ').');
        }

        $this->config['vcCode'] = $config['vcCode'];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * It calls the MyFatoorah API endpoint request and handles the MyFatoorah API endpoint response.
     *
     * @param string          $url        MyFatoorah API endpoint URL
     * @param array|null      $postFields POST request parameters array. It should be set to null if the request is GET.
     * @param int|string|null $orderId    The order id or the payment id of the process, used for the events logging.
     * @param string|null     $function   The requester function name, used for the events logging. ex:__FUNCTION__.
     *
     * @return mixed The response object as the result of a successful calling to the API.
     *
     * @throws Exception    Throw exception if there is any curl/validation error in the MyFatoorah API endpoint URL.
     */
    public function callAPI($url, $postFields = null, $orderId = null, $function = null)
    {
        // Prevent json_encode from adding lots of decimal digits
        ini_set('precision', '14');
        ini_set('serialize_precision', '-1');

        $request = isset($postFields) ? 'POST' : 'GET';
        $fields  = empty($postFields) ? json_encode($postFields, JSON_FORCE_OBJECT) : json_encode($postFields, JSON_UNESCAPED_UNICODE);

        $msgLog = "Order #$orderId ----- $function";
        $this->log("$msgLog - Request: $fields");

        //***************************************
        // Call API url
        //***************************************
        $curl = curl_init($url);

        $options = [
            CURLOPT_CUSTOMREQUEST  => $request,
            CURLOPT_POSTFIELDS     => $fields,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->config['apiKey'], 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true
        ];

        curl_setopt_array($curl, $options);

        $res = curl_exec($curl);
        $err = curl_error($curl);

        // Release the handle; curl_close() is a no-op since PHP 8.0 and deprecated since 8.5
        unset($curl);

        // Check for cURL errors
        if ($err) {
            $this->log("$msgLog - cURL Error: $err");
            throw new Exception('cURL Error: ' . $err);
        }

        $this->log("$msgLog - Response: $res");

        $json = json_decode((string) $res);

        //***************************************
        // Check for response errors
        //***************************************
        $error = self::getAPIError($json, (string) $res);
        if ($error) {
            $this->log("$msgLog - Error: $error");
            throw new Exception($error);
        }

        //***************************************
        // Success
        //***************************************
        return $json;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Handle Endpoint Errors Function
     *
     * @param mixed  $json MyFatoorah response JSON object.
     * @param string $res  MyFatoorah response string.
     *
     * @return string
     */
    protected static function getAPIError($json, $res)
    {
        $isSuccess = $json->IsSuccess ?? false;
        if ($isSuccess) {
            return '';
        }

        // Check for the HTML errors
        $hErr = self::getHtmlErrors($res);
        if ($hErr) {
            return $hErr;
        }

        // Check for the JSON errors
        if (is_string($json)) {
            return $json;
        }

        if (empty($json)) {
            return (!empty($res) ? $res : 'Kindly review your MyFatoorah admin configuration due to a wrong entry.');
        }

        return self::getJsonErrors($json);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Check for the HTML (response model) errors
     *
     * @param string $res MyFatoorah response string.
     *
     * @return string
     */
    protected static function getHtmlErrors($res)
    {
        // To avoid blocked IP like:
        // <html>
        // <head><title>403 Forbidden</title></head>
        // <body>
        // <center><h1>403 Forbidden</h1></center><hr><center>Microsoft-Azure-Application-Gateway/v2</center>
        // </body>
        // </html>
        // and, skip apple register <YourDomainName> tag error
        $stripHtml = strip_tags($res);
        if ($res != $stripHtml && stripos($stripHtml, 'apple-developer-merchantid-domain-association') !== false) {
            return trim(preg_replace('/\s+/', ' ', $stripHtml));
        }
        return '';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Check for the json (response model) errors
     *
     * @param mixed $json MyFatoorah response JSON object.
     *
     * @return string
     */
    protected static function getJsonErrors($json)
    {
        $errorsVar = isset($json->ValidationErrors) ? 'ValidationErrors' : 'FieldsErrors';
        if (isset($json->$errorsVar)) {
            $blogDatas = array_column($json->$errorsVar, 'Error', 'Name');

            $mapFun = function ($k, $v) {
                return "$k: $v";
            };
            $errArr = array_map($mapFun, array_keys($blogDatas), array_values($blogDatas));

            return implode(', ', $errArr);
            //return implode(', ', array_column($json->ValidationErrors, 'Error'));
        }

        if (isset($json->Data->ErrorMessage)) {
            return $json->Data->ErrorMessage;
        }

        // If not, get the message.
        // sometimes Error value of ValidationErrors is null, so either get the "Name" key or get the "Message"
        // example {
        // "IsSuccess":false,
        // "Message":"Invalid data",
        // "ValidationErrors":[{"Name":"invoiceCreate.InvoiceItems","Error":""}],
        // "Data":null
        // }
        // example {
        // "Message":
        // "No HTTP resource was found that matches the request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'.",
        // "MessageDetail":
        // "No route providing a controller name was found to match request URI
        // 'https://apitest.myfatoorah.com/v2/SendPayment222'"
        // }
        return empty($json->Message) ? '' : $json->Message;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Log the events
     *
     * @param string $msg It is the string message that will be written in the log file.
     *
     * @return void
     */
    public static function log($msg)
    {
        $loggerObj  = self::$loggerObj;
        $loggerFunc = self::$loggerFunc;

        if (empty($loggerObj)) {
            return;
        }

        if (is_string($loggerObj)) {
            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - ' . $msg, 3, $loggerObj);
        } elseif (method_exists($loggerObj, $loggerFunc)) {
            $loggerObj->{$loggerFunc}($msg);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the vendor time zone based on the configured vendor country code.
     *
     * In test mode, the Kuwait time zone is always used.
     * In live mode, the time zone is resolved from the vendor country configuration.
     *
     * @return string The vendor time zone.
     */
    protected function getVendorTimeZone()
    {
        $countries = self::getMFCountries();

        $vcCode = $this->config['vcCode'];
        $isTest = $this->config['isTest'];

        return ($isTest) ? $countries['KWT']['timeZone'] : $countries[$vcCode]['timeZone'];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the localized ExpiryDate based on the vendor’s country using ExpiryMinutes.
     *
     * @param int|string $expiryMinutes The number of minutes after which the invoice expires.
     *
     * @return string
     */
    public function getExpiryDate($expiryMinutes)
    {
        if (!ctype_digit((string) $expiryMinutes) || (int) $expiryMinutes <= 0) {
            return '';
        }

        $timeZone = $this->getVendorTimeZone();

        $nowDate = new \DateTime('now', new \DateTimeZone($timeZone));
        $nowDate->modify("+$expiryMinutes minutes");

        return $nowDate->format('Y-m-d\TH:i:s');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
