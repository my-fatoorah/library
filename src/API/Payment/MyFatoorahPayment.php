<?php

namespace MyFatoorah\Library\API\Payment;

use MyFatoorah\Library\MyFatoorah;
use Exception;

/**
 *  MyFatoorahPayment handles the payment process of MyFatoorah API endpoints
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorahPayment extends MyFatoorah
{

    /**
     * The file name used in caching the gateways data
     *
     * @var string
     */
    public static $pmCachedFile = __DIR__ . '/mf-methods.json';

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * List available Payment Methods (POST API)
     *
     * @param double|int $invoiceAmount The display invoice total amount.
     * @param string     $currencyIso   The display invoice currency ISO.
     * @param boolean    $isCached      It used to cache the gateways.
     *
     * @return array
     */
    public function initiatePayment($invoiceAmount = 0, $currencyIso = '', $isCached = false)
    {

        $postFields = [
            'InvoiceAmount' => $invoiceAmount,
            'CurrencyIso'   => $currencyIso,
        ];

        $json = $this->callAPI("$this->apiURL/v2/InitiatePayment", $postFields, null, 'Initiate Payment');

        $paymentMethods = ($json->Data->PaymentMethods) ?? [];

        if (!empty($paymentMethods) && $isCached) {
            file_put_contents(self::$pmCachedFile, json_encode($paymentMethods));
        }
        return $paymentMethods;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * List available Cached Payment Gateways
     *
     * @return mixed of Cached payment methods.
     */
    public function getCachedVendorGateways()
    {

        if (file_exists(self::$pmCachedFile)) {
            $cache = file_get_contents(self::$pmCachedFile);
            return ($cache) ? json_decode($cache) : [];
        } else {
            return $this->initiatePayment(0, '', true);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * List available cached  Payment Methods
     *
     * @param bool $isApRegistered Is site domain is registered with applePay and MyFatoorah or not
     *
     * @return array
     */
    public function getCachedCheckoutGateways($isApRegistered = false)
    {

        $gateways = $this->getCachedVendorGateways();

        $cachedGateways = ['all' => [], 'cards' => [], 'form' => [], 'ap' => [], 'gp' => []];
        foreach ($gateways as $gateway) {
            $cachedGateways = $this->addGatewayToCheckout($gateway, $cachedGateways, $isApRegistered);
        }

        if ($isApRegistered) {
            //add only one ap gateway
            $cachedGateways['ap'] = $cachedGateways['ap'][0] ?? [];
        }

        return $cachedGateways;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Add the MyFatoorah gateway object to the a given Payment Methods Array
     *
     * @param object  $gateway          MyFatoorah gateway object.
     * @param array   $checkoutGateways Payment Methods Array.
     * @param boolean $isApRegistered   Is site domain is registered with applePay and MyFatoorah or not.
     *
     * @return array
     */
    protected function addGatewayToCheckout($gateway, $checkoutGateways, $isApRegistered)
    {

        if ($gateway->PaymentMethodCode == 'gp') {
            $checkoutGateways['gp']    = $gateway;
            $checkoutGateways['all'][] = $gateway;
        } elseif ($gateway->PaymentMethodCode == 'ap') {
            if ($isApRegistered) {
                $checkoutGateways['ap'][] = $gateway;
            } else {
                $checkoutGateways['cards'][] = $gateway;
            }
            $checkoutGateways['all'][] = $gateway;
        } elseif ($gateway->PaymentMethodCode == 'stc') {
            $checkoutGateways['cards'][] = $gateway;
            $checkoutGateways['all'][]   = $gateway;
        } else {
            if ($gateway->IsEmbeddedSupported) {
                $checkoutGateways['form'][] = $gateway;
                $checkoutGateways['all'][]  = $gateway;
            } elseif (!$gateway->IsDirectPayment) {
                $checkoutGateways['cards'][] = $gateway;
                $checkoutGateways['all'][]   = $gateway;
            }
        }

        return $checkoutGateways;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get Payment Method Object
     *
     * @param string     $gateway       MyFatoorah gateway object.
     * @param string     $searchKey     The Search key ['PaymentMethodId', 'PaymentMethodCode'].
     * @param double|int $invoiceAmount The display invoice total amount.
     * @param string     $currencyIso   The display invoice currency ISO.
     *
     * @return object
     *
     * @throws Exception
     */
    public function getOnePaymentMethod($gateway, $searchKey = 'PaymentMethodId', $invoiceAmount = 0, $currencyIso = '')
    {

        $paymentMethods = $this->initiatePayment($invoiceAmount, $currencyIso);

        $paymentMethod = null;
        foreach ($paymentMethods as $pm) {
            if ($pm->$searchKey == $gateway) {
                $paymentMethod = $pm;
                break;
            }
        }

        if (!isset($paymentMethod)) {
            throw new Exception('Please contact Account Manager to enable the used payment method in your account');
        }

        return $paymentMethod;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the invoice/payment URL and the invoice id
     *
     * @param array      $curlData  Invoice information.
     * @param int|string $gatewayId MyFatoorah Gateway ID (default value: '0').
     * @param int|string $orderId   It used in log file (default value: null).
     * @param string     $sessionId The payment session used in embedded payment.
     * @param string     $ntfOption The notificationOption for send payment. It could be EML, SMS, LNK, or ALL.
     *
     * @return array of invoiceURL and invoiceURL
     */
    public function getInvoiceURL($curlData, $gatewayId = 0, $orderId = null, $sessionId = null, $ntfOption = 'Lnk')
    {

        $this->log('------------------------------------------------------------');

        $curlData['CustomerReference'] = $curlData['CustomerReference'] ?? $orderId;

        if (!empty($sessionId)) {
            $curlData['SessionId'] = $sessionId;

            $data = $this->executePayment($curlData);
            return ['invoiceURL' => $data->PaymentURL, 'invoiceId' => $data->InvoiceId];
        } elseif ($gatewayId == 'myfatoorah' || empty($gatewayId)) {
            if (empty($curlData['NotificationOption'])) {
                $curlData['NotificationOption'] = $ntfOption;
            }

            $data = $this->sendPayment($curlData);
            return ['invoiceURL' => $data->InvoiceURL, 'invoiceId' => $data->InvoiceId];
        } else {
            $curlData['PaymentMethodId'] = $gatewayId;

            $data = $this->executePayment($curlData);
            return ['invoiceURL' => $data->PaymentURL, 'invoiceId' => $data->InvoiceId];
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Create an invoice Link (POST API)
     *
     * @param array $curlData Invoice information, check https://docs.myfatoorah.com/docs/send-payment#request-model.
     *
     * @return object
     */
    public function sendPayment($curlData)
    {

        $this->preparePayment($curlData);

        $json = $this->callAPI("$this->apiURL/v2/SendPayment", $curlData, $curlData['CustomerReference'], 'Send Payment');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Create an Payment Link (POST API)
     *
     * @param array $curlData Invoice information, check https://docs.myfatoorah.com/docs/execute-payment#request-model.
     *
     * @return object
     */
    public function executePayment($curlData)
    {

        $this->preparePayment($curlData);

        $json = $this->callAPI("$this->apiURL/v2/ExecutePayment", $curlData, $curlData['CustomerReference'], 'Execute Payment');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Prepare payment array for SendPayment and ExecutePayment
     *
     * @param array $curlData Invoice information
     */
    private function preparePayment(&$curlData)
    {

        $curlData['CustomerReference'] = $curlData['CustomerReference'] ?? null;
        $curlData['SourceInfo']        = $curlData['SourceInfo'] ?? 'MyFatoorah PHP Library ' . $this->version;

        if (!empty($curlData['CustomerName'])) {
            $curlData['CustomerName'] = preg_replace('/[^\p{L}\p{N}\s]/u', '', $curlData['CustomerName']);
        }

        if (!empty($curlData['InvoiceItems'])) {
            foreach ($curlData['InvoiceItems'] as &$item) {
                $item['ItemName'] = strip_tags($item['ItemName']);
            }
        }

        if (empty($curlData['CustomerEmail'])) {
            $curlData['CustomerEmail'] = null;
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get session Data
     *
     * @param string     $userDefinedField Customer Identifier to display its saved data.
     * @param int|string $logId            It used in log file, example you can use the orderId (default value: null).
     *
     * @return object
     */
    public function getEmbeddedSession($userDefinedField = '', $logId = null)
    {

        $curlData = ['CustomerIdentifier' => $userDefinedField];

        return $this->InitiateSession($curlData, $logId);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get session Data (POST API)
     *
     * @param array      $curlData Session properties.
     * @param int|string $logId    It used in log file, example you can use the orderId (default value: null).
     *
     * @return object
     */
    public function InitiateSession($curlData, $logId = null)
    {

        $json = $this->callAPI("$this->apiURL/v2/InitiateSession", $curlData, $logId, 'Initiate Session');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Register Apple Pay Domain (POST API)
     *
     * @param string $url Site URL
     *
     * @return object
     */
    public function registerApplePayDomain($url)
    {

        $domainName = ['DomainName' => parse_url($url, PHP_URL_HOST)];
        return $this->callAPI("$this->apiURL/v2/RegisterApplePayDomain", $domainName, '', 'Register Apple Pay Domain');
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
