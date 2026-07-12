<?php

namespace MyFatoorah\Library;

use Exception;

/**
 *  MyFatoorahWebhook handles Webhook endpoints.
 */
class MyFatoorahWebhook extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    public static function processWebhookRequest($secretKey, $logger = __DIR__ . '/myfatoorah_webhook.log'/* , $request = null */)
    {
        MyFatoorah::$loggerObj = $logger;
        MyFatoorah::log('MyFatoorah WebHook New Request');

        if (!$secretKey) {
            $msg = 'Store needs to be configured.';
            MyFatoorah::log($msg);
            throw new Exception($msg);
        }

        list($mfVersion, $signature) = self::getMfHeaders();

        //        if (!$request) {
        $body = file_get_contents('php://input');
        MyFatoorah::log('MyFatoorah WebHook Body: ' . $body);

        $request = json_decode($body, true);
        //        }

        if (empty($request['Data'])) {
            $msg = 'Wrong data.';
            MyFatoorah::log($msg);
            throw new Exception($msg);
        }

        if (self::{"checkSignatureValidation$mfVersion"}($request, $secretKey, $signature)) {
            return $request;
        }

        $msg = 'Validation error.';
        MyFatoorah::log($msg);
        throw new Exception($msg);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Returns the MyFatoorah signature and version
     *
     * @return array<string>
     * @throws Exception if the header contains a wrong headers
     */
    protected static function getMfHeaders()
    {
        $apache  = (array) apache_request_headers();
        $headers = array_change_key_case($apache);

        if (empty($headers['myfatoorah-signature']) || empty($headers['myfatoorah-webhook-version'])) {
            throw new Exception('Wrong request.');
        }

        $mfVersion = strtolower($headers['myfatoorah-webhook-version']);
        if ($mfVersion != 'v1' && $mfVersion != 'v2') {
            throw new Exception('Wrong version.');
        }
        return [$mfVersion, $headers['myfatoorah-signature']];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Checks whether the provided signature is correct or not for MyFatoorah Webhook version 1
     *
     * @param array<mixed> $request
     * @param string       $secretKey
     * @param string       $signature
     *
     * @return boolean
     *
     * @throws Exception if something wrong in the request
     */
    protected static function checkSignatureValidationV1($request, $secretKey, $signature)
    {
        if (!isset($request['EventType']) || !isset($request['Event'])) {
            throw new Exception('Worng event.');
        }

        return MyFatoorah::isSignatureValid($request['Data'], $secretKey, $signature, $request['EventType']);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Checks whether the provided signature is correct or not for MyFatoorah Webhook version 2
     *
     * @param array<mixed, mixed> $request
     * @param string              $secretKey
     * @param string              $signature
     *
     * @return boolean
     *
     * @throws Exception if something wrong in the request
     */
    protected static function checkSignatureValidationV2($request, $secretKey, $signature)
    {
        if (!isset($request['Event']['Code']) || !isset($request['Event']['Name'])) {
            throw new Exception('Worng event.');
        }

        $dataModel = self::getV2DataModel($request['Event']['Code'], $request['Data']);
        return self::checkSignatureValidation($dataModel, $secretKey, $signature);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Returns the correct data model of the event
     *
     * @param int                 $code The event code
     * @param array<string,mixed> $data The event data
     *
     * @return array<string, mixed>
     *
     * @throws Exception if the event code is not correct
     */
    private static function getV2DataModel($code, $data)
    {
        $dataModels = [
            //https://docs.myfatoorah.com/docs/webhook-v2-payment-status-data-model
            //Invoice.Id=6409988,Invoice.Status=PAID,Transaction.Status=SUCCESS,Transaction.PaymentId=07076409988323998875,Invoice.ExternalIdentifier=asdqwd-f13sdf-fasjkz
            1 => function () use ($data) {
                return [
                    'Invoice.Id'                 => $data['Invoice']['Id'],
                    'Invoice.Status'             => $data['Invoice']['Status'],
                    'Transaction.Status'         => $data['Transaction']['Status'],
                    'Transaction.PaymentId'      => $data['Transaction']['PaymentId'],
                    'Invoice.ExternalIdentifier' => $data['Invoice']['ExternalIdentifier'],
                ];
            },
            //https://docs.myfatoorah.com/docs/webhook-v2-refund-data-model
            2 => function () use ($data) {
                return [
                    'Refund.Id'                  => $data['Refund']['Id'],
                    'Refund.Status'              => $data['Refund']['Status'],
                    'Amount.ValueInBaseCurrency' => $data['Amount']['ValueInBaseCurrency'],
                    'ReferencedInvoice.Id'       => $data['ReferencedInvoice']['Id'],
                ];
            },
            //https://docs.myfatoorah.com/docs/webhook-v2-balance-transferred-data-model
            3 => function () use ($data) {
                return [
                    'Deposit.Reference'            => $data['Deposit']['Reference'],
                    'Deposit.ValueInBaseCurrency'  => $data['Deposit']['ValueInBaseCurrency'],
                    'Deposit.NumberOfTransactions' => $data['Deposit']['NumberOfTransactions'],
                ];
            },
            //https://docs.myfatoorah.com/docs/webhook-v2-supplier-data-model
            4 => function () use ($data) {
                return [
                    'Supplier.Code'      => $data['Supplier']['Code'],
                    'KycDecision.Status' => $data['KycDecision']['Status'],
                ];
            },
            //https://docs.myfatoorah.com/docs/webhook-v2-recurring-data-model
            5 => function () use ($data) {
                return [
                    'Recurring.Id'               => $data['Recurring']['Id'],
                    'Recurring.Status'           => $data['Recurring']['Status'],
                    'Recurring.InitialInvoiceId' => $data['Recurring']['InitialInvoiceId'],
                ];
            }
        ];

        if (!isset($dataModels[$code])) {
            throw new Exception('Worng event.');
        }

        return $dataModels[$code]();
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    public static function checkforWebHook2ProcessMessage($webhook, $order)
    {
        //        if (strpos($order['myfatoorah_orderPM'], 'myfatoorah') === false) {
        //            return('Wrong Payment Method.');
        //        }

        if ($order['myfatoorah_invoiceId'] != $webhook['Invoice']['Id']) {
            return('Wrong invoice.');
        }

        //don't process because the Paid is a final status
        if ($order['myfatoorah_status'] == 'Paid') {
            return('Order already Paid');
        }

        //don't process for the same payment id and the status is not SUCCESS
        if ($order['myfatoorah_paymentId'] == $webhook['Transaction']['PaymentId']) {
            return "Transaction already {$webhook['Transaction']['Status']}.";
        }

        return false;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public static function mapWebhook2Status($status)
    {
        $statuses = [
            'SUCCESS'  => 'Paid',
            'FAILED'   => 'Failed',
            'CANCELED' => 'Expired',
            'PENDING'  => 'Pending',
        ];
        return $statuses[$status] ?? null;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
