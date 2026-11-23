<?php

namespace MyFatoorah\Test\API\Payment;

use MyFatoorah\Test\MyFatoorahTestCase;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;

class MyFatoorahPaymentStatusTest extends MyFatoorahTestCase
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetPaymentStatus(): void
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahPaymentStatus($config);
                $data  = $mfObj->getPaymentStatus('100202312116138082', 'PaymentId');

                $this->assertEquals('Paid', $data->InvoiceStatus);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
