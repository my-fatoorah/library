<?php

namespace MyFatoorah\Test\API\Payment;

use MyFatoorah\Test\MyFatoorahTestCase;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentEmbedded;

class MyFatoorahPaymentEmbeddedTest extends MyFatoorahTestCase
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetCheckoutGateways(): void
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahPaymentEmbedded($config);
                $data  = $mfObj->getCheckoutGateways(10, 'KWD', false);

                $this->assertArrayHasKey('PaymentMethodId', (array) $data['all'][0]);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
