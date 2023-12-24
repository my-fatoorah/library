<?php

namespace MyFatoorah\Library\API\Payment;

use Exception;

/**
 *  MyFatoorahPaymentStatus handles the payment status of MyFatoorah API endpoints
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright 2021 MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorahPaymentStatus extends MyFatoorahPayment
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the Payment Transaction Status (POST API)
     *
     * @param int|string $keyId    MyFatoorah InvoiceId, PaymentId, or CustomerReference value.
     * @param string     $KeyType  The supported key types are "InvoiceId", "PaymentId", or "CustomerReference".
     * @param int|string $orderId  The order id in the store used to match the invoice data with store order.
     * @param string     $price    The order price in the store used to match the invoice data with store order.
     * @param string     $currency The order currency in the store used to match the invoice data with store order.
     *
     * @return object
     *
     * @throws Exception
     */
    public function getPaymentStatus($keyId, $KeyType, $orderId = null, $price = null, $currency = null)
    {

        //payment inquiry
        $curlData = ['Key' => $keyId, 'KeyType' => $KeyType];
        $json     = $this->callAPI("$this->apiURL/v2/GetPaymentStatus", $curlData, $orderId, 'Get Payment Status');

        $data = $json->Data;

        $msgLog = 'Order #' . $data->CustomerReference . ' ----- Get Payment Status';

        //check for the order information
        if (!self::checkOrderInformation($data, $orderId, $price, $currency)) {
            $err = 'Trying to call data of another order';
            $this->log("$msgLog - Exception is $err");
            throw new Exception($err);
        }

        //check invoice status (Paid and Not Paid Cases)
        if ($data->InvoiceStatus == 'Paid' || $data->InvoiceStatus == 'DuplicatePayment') {
            $data = self::getSuccessData($data);
            $this->log("$msgLog - Status is Paid");
        } elseif ($data->InvoiceStatus != 'Paid') {
            $data = self::getErrorData($data, $keyId, $KeyType);
            $this->log("$msgLog - Status is " . $data->InvoiceStatus . '. Error is ' . $data->InvoiceError);
        }

        return $data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Validate the invoice data with store order data
     *
     * @param object     $data     The MyFatoorah invoice data
     * @param int|string $orderId  The order id in the store used to match the invoice data with store order.
     * @param string     $price    The order price in the store used to match the invoice data with store order.
     * @param string     $currency The order currency in the store used to match the invoice data with store order.
     *
     * @return boolean
     */
    private static function checkOrderInformation($data, $orderId = null, $price = null, $currency = null)
    {

        //check for the order ID
        if ($orderId && $orderId != $data->CustomerReference) {
            return false;
        }

        //check for the order price and currency
        list($valStr, $mfCurrency) = explode(' ', $data->InvoiceDisplayValue);
        $mfPrice = floatval(preg_replace('/[^\d.]/', '', $valStr));

        if ($price && $price != $mfPrice) {
            return false;
        }

        return !($currency && $currency != $mfCurrency);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Search for the success transaction and return it in the focusTransaction object
     *
     * @param object $data The payment data object
     *
     * @return object
     */
    private static function getSuccessData($data)
    {

        foreach ($data->InvoiceTransactions as $transaction) {
            if ($transaction->TransactionStatus == 'Succss') {
                $data->InvoiceStatus = 'Paid';
                $data->InvoiceError  = '';

                $data->focusTransaction = $transaction;
                return $data;
            }
        }
        return $data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Search for the failed transaction and return it in the focusTransaction object
     *
     * @param object     $data    The payment data object
     * @param int|string $keyId   MyFatoorah InvoiceId, PaymentId, or CustomerReference value.
     * @param string     $KeyType The supported key types are "InvoiceId", "PaymentId", or "CustomerReference".
     *
     * @return object
     */
    private static function getErrorData($data, $keyId, $KeyType)
    {

        //------------------
        //case 1: payment is Failed
        $focusTransaction = self::{"getLastTransactionOf$KeyType"}($data->InvoiceTransactions, $keyId);
        if ($focusTransaction && $focusTransaction->TransactionStatus == 'Failed') {
            $data->InvoiceStatus = 'Failed';
            $data->InvoiceError  = $focusTransaction->Error . '.';

            $data->focusTransaction = $focusTransaction;

            return $data;
        }

        //------------------
        //case 2: payment is Expired
        //all myfatoorah gateway is set to Asia/Kuwait
        $ExpiryDateTime = $data->ExpiryDate . ' ' . $data->ExpiryTime;
        $ExpiryDate     = new \DateTime($ExpiryDateTime, new \DateTimeZone('Asia/Kuwait'));
        $currentDate    = new \DateTime('now', new \DateTimeZone('Asia/Kuwait'));

        if ($ExpiryDate < $currentDate) {
            $data->InvoiceStatus = 'Expired';
            $data->InvoiceError  = 'Invoice is expired since ' . $data->ExpiryDate . '.';

            return $data;
        }

        //------------------
        //case 3: payment is Pending
        //payment is pending .. user has not paid yet and the invoice is not expired
        $data->InvoiceStatus = 'Pending';
        $data->InvoiceError  = 'Pending Payment.';

        return $data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Search for the failed transaction by the payment Id
     *
     * @param array      $transactions The transactions array
     * @param int|string $paymentId    the failed payment Id
     *
     * @return object|null
     */
    private static function getLastTransactionOfPaymentId($transactions, $paymentId)
    {

        foreach ($transactions as $transaction) {
            if ($transaction->PaymentId == $paymentId && $transaction->Error) {
                return $transaction;
            }
        }
        return null;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Search for the last transaction of an invoice
     *
     * @param array $transactions The transactions array
     *
     * @return object
     */
    private static function getLastTransactionOfInvoiceId($transactions)
    {

        $usortFun = function ($a, $b) {
            return strtotime($a->TransactionDate) - strtotime($b->TransactionDate);
        };
        usort($transactions, $usortFun);

        return end($transactions);
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
    public function getPaymentStatusLink($url, $paymentId)
    {
        //to overcome session urls
        $pattern = '/MpgsAuthentication.*|ApplePayComplete.*|GooglePayComplete.*/i';
        return preg_replace($pattern, "Result?paymentId=$paymentId", $url);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
