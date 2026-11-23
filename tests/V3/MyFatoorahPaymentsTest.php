<?php

namespace MyFatoorah\Test\V3;

use MyFatoorah\Test\MyFatoorahTestCase;
use MyFatoorah\Library\V3\MyFatoorahPayments;

class MyFatoorahPaymentsTest extends MyFatoorahTestCase
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testCreatePayment(): void
    {
        $curlData = [
            'SessionId' => 'test-session-id-001',
            'Order'     => [
                'ExternalIdentifier' => 'test-ref-v3-001',
                'Amount'             => 10,
                'CurrencyIso'        => 'KWD',
            ],
        ];

        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahPayments($config);
                $data  = $mfObj->createPayment($curlData);

                $this->assertObjectHasProperty('PaymentURL', $data);
                $this->assertStringStartsWith('https://', $data->PaymentURL);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetPaymentDetails(): void
    {
        $paymentId = '07076945628364902373';

        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahPayments($config);
                $data  = $mfObj->getPaymentDetails($paymentId);
                $this->assertObjectHasProperty('Invoice', $data);

                $this->assertEquals('PAID', $data->Invoice->Status);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
