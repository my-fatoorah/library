<?php

namespace MyFatoorah\Library\V3;

use MyFatoorah\Library\MyFatoorah;

/**
 * Handles v3 payment APIs.
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorahPayments extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Process a payment using a collected session (Mode 2).
     * POST /v3/payments
     *
     * @param array $data
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
     * @param string $paymentId
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
