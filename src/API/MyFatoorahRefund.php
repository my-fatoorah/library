<?php

namespace MyFatoorah\Library\API;

use MyFatoorah\Library\MyFatoorah;

class MyFatoorahRefund extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * (deprecated function) use makeRefund instead
     * Refund a given PaymentId or InvoiceId
     *
     * @param int|string       $keyId    payment id that will be refunded
     * @param float|int|string $amount   the refund amount
     * @param string           $currency the amount currency
     * @param string           $comment  reason of the refund
     * @param int|string       $orderId  used in log file (default value: null)
     * @param string           $keyType  supported keys are (InvoiceId, PaymentId)
     *
     * @return object
     *
     * @deprecated
     */
    public function refund($keyId, $amount, $currency = null, $comment = null, $orderId = null, $keyType = 'PaymentId')
    {
        $postFields = [
            'Key'                     => $keyId,
            'KeyType'                 => $keyType,
            'RefundChargeOnCustomer'  => false,
            'ServiceChargeOnCustomer' => false,
            'Amount'                  => $amount,
            'CurrencyIso'             => $currency,
            'Comment'                 => $comment,
        ];

        return $this->makeRefund($postFields, $orderId);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Call makeRefund API (POST API)
     *
     * @param array      $curlData Refund information
     * @param int|string $logId    Used in log file, example you can use the orderId (default value: null)
     *
     * @return object
     */
    public function makeRefund($curlData, $logId = null)
    {
        $url  = "$this->apiURL/v2/MakeRefund";
        $json = $this->callAPI($url, $curlData, $logId, 'Make Refund');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
