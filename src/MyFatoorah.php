<?php

namespace MyFatoorah\Library;

use Exception;

/**
 * MyFatoorah is responsible for handling calling MyFatoorah API endpoints.
 * Also, It has necessary library functions that help in providing the correct parameters used endpoints.
 *
 * MyFatoorah offers a seamless business experience by offering a technology put together by our tech team.
 * It enables smooth business operations involving sales activity, product invoicing, shipping, and payment processing.
 * MyFatoorah invoicing and payment gateway solution trigger your business to greater success at all levels in the new age world of commerce. Leverage your sales and payments at all e-commerce platforms (ERPs, CRMs, CMSs) with transparent and slick applications that are well-integrated into social media and telecom services. For every closing sale click, you make a business function gets done for you, along with generating factual reports and statistics to fine-tune your business plan with no-barrier low-cost. Our technology experts have designed the best GCC E-commerce solutions for the native financial instruments (Debit Cards, Credit Cards, etc.) supporting online sales and payments, for events, shopping, mall, and associated services.
 *
 * Created by MyFatoorah http://www.myfatoorah.com/
 * Developed By tech@myfatoorah.com
 * Date: 31/03/2024
 * Time: 12:00
 *
 * API Documentation on https://myfatoorah.readme.io/docs
 * Library Documentation and Download link on https://myfatoorah.readme.io/docs/php-library
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorah extends MyFatoorahHelper
{

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
        $this->setVcCode($config);

        $this->config['loggerObj']  = empty($config['loggerObj']) ? null : $config['loggerObj'];
        $this->config['loggerFunc'] = empty($config['loggerFunc']) ? null : $config['loggerFunc'];

        //to use logger as static
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
     * @param array $config It has the required keys (apiKey, isTest, and vcCode) to process a MyFatoorah API request.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setVcCode($config)
    {
        $config['vcCode'] = $config['vcCode'] ?? $config['countryCode'] ?? '';
        if (empty($config['vcCode'])) {
            throw new Exception('Config array must have the "vcCode" key.');
        }

        $mfCountries    = self::getMFCountries();
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
     * @return mixed       The response object as the result of a successful calling to the API.
     *
     * @throws Exception    Throw exception if there is any curl/validation error in the MyFatoorah API endpoint URL.
     */
    public function callAPI($url, $postFields = null, $orderId = null, $function = null)
    {

        //to prevent json_encode adding lots of decimal digits
        ini_set('precision', '14');
        ini_set('serialize_precision', '-1');

        $request = isset($postFields) ? 'POST' : 'GET';
        $fields  = empty($postFields) ? json_encode($postFields, JSON_FORCE_OBJECT) : json_encode($postFields);

        $msgLog = "Order #$orderId ----- $function";
        $this->log("$msgLog - Request: $fields");

        //***************************************
        //call url
        //***************************************
        $curl = curl_init($url);

        $option = [
            CURLOPT_CUSTOMREQUEST  => $request,
            CURLOPT_POSTFIELDS     => $fields,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->config['apiKey'], 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true
        ];

        curl_setopt_array($curl, $option);

        $res = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        //example set a local ip to host apitest.myfatoorah.com
        if ($err) {
            $this->log("$msgLog - cURL Error: $err");
            throw new Exception($err);
        }

        $this->log("$msgLog - Response: $res");

        $json = json_decode((string) $res);

        //***************************************
        //check for errors
        //***************************************
        //Check for the reponse errors
        $error = self::getAPIError($json, (string) $res);
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

        //Check for the HTML errors
        $hErr = self::getHtmlErrors($res);
        if ($hErr) {
            return $hErr;
        }

        //Check for the JSON errors
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
        //to avoid blocked IP like:
        //<html>
        //<head><title>403 Forbidden</title></head>
        //<body>
        //<center><h1>403 Forbidden</h1></center><hr><center>Microsoft-Azure-Application-Gateway/v2</center>
        //</body>
        //</html>
        //and, skip apple register <YourDomainName> tag error
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

        //if not, get the message.
        //sometimes Error value of ValidationErrors is null, so either get the "Name" key or get the "Message"
        //example {
        //"IsSuccess":false,
        //"Message":"Invalid data",
        //"ValidationErrors":[{"Name":"invoiceCreate.InvoiceItems","Error":""}],
        //"Data":null
        //}
        //example {
        //"Message":
        //"No HTTP resource was found that matches the request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'.",
        //"MessageDetail":
        //"No route providing a controller name was found to match request URI 
        //'https://apitest.myfatoorah.com/v2/SendPayment222'"
        //}

        return empty($json->Message) ? '' : $json->Message;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Log the events
     *
     * @param string $msg It is the string message that will be written in the log file.
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
}
