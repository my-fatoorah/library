<?php

namespace MyFatoorah\Test\API;

use MyFatoorah\Test\MyFatoorahTestCase;
use MyFatoorah\Library\API\MyFatoorahRefund;

class MyFatoorahRefundTest extends MyFatoorahTestCase
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testRefund(): void
    {
        try {
            $mfObj = new MyFatoorahRefund($this->keys['valid']);

            $json = $mfObj->refund('100202312116138082', 10, 'KWD', 'test');

            $this->assertEquals('100202312116138082', $json->Key);
            $this->assertNotNull($json->RefundReference);
        } catch (\Exception $ex) {
            $this->assertContains(
                $ex->getMessage(),
                [
                    $this->keys['valid']['refundException'],
                    $this->keys['valid']['refundException2'],
                ],
                $this->keys['valid']['message']
            );
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testMakeRefund(): void
    {
        $postFields = [
            'Key'                     => 100202312116138082,
            'KeyType'                 => 'PaymentId',
            'RefundChargeOnCustomer'  => false, 'ServiceChargeOnCustomer' => false,
            'Amount'                  => 2,
            'CurrencyIso'             => 'KWD',
            'Comment'                 => 'test'
        ];

        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahRefund($config);
                $json  = $mfObj->makeRefund($postFields);

                $this->assertEquals('100202312116138082', $json->Key);
                $this->assertNotNull($json->RefundReference);
            } catch (\Exception $ex) {
                $exception = $config['refundException'] ?? $config['exception'];
                $this->assertEquals($exception, $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
