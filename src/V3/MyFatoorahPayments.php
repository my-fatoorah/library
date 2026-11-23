<?php

namespace MyFatoorah\Library\V3;

use MyFatoorah\Library\MyFatoorah;

class MyFatoorahPayments extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Process a payment using a collected session (Mode 2).
     * POST /v3/payments
     *
     * @param array $data the payment request body
     *
     * @return object
     */
    public function createPayment(array $data)
    {
        $reference = $data['Order']['ExternalIdentifier'] ?? null;
        $json      = $this->callAPI($this->apiURL . '/v3/payments', $data, $reference, 'CreatePayment');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get payment status by paymentId.
     * GET /v3/payments/{paymentId}
     *
     * @param string $paymentId the MyFatoorah payment id
     *
     * @return object
     */
    public function getPaymentDetails($paymentId)
    {
        $json = $this->callAPI($this->apiURL . '/v3/payments/' . $paymentId, null, $paymentId, 'getPaymentDetails');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
