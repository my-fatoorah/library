<?php

namespace MyFatoorah\Test\API;

use PHPUnit\Framework\TestCase;
use MyFatoorah\Library\API\MyFatoorahRefund;

class MyFatoorahRefundTest extends TestCase
{

    private $keys;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->keys = include __DIR__ . '/../apiKeys.php';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testRefund()
    {
        try {
            $mfObj = new MyFatoorahRefund($this->keys['valid']);

            $json = $mfObj->refund('100202312116138082', 10, 'KWD', 'test');

            $this->assertEquals('100202312116138082', $json->Key);
            $this->assertNotNull($json->RefundReference);
        } catch (\Exception $ex) {
            $this->assertEquals($this->keys['valid']['refundException'], $ex->getMessage(), $this->keys['valid']['message']);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testMakeRefund()
    {
        $postFields = [
            'Key' => 100202312116138082,
            'KeyType' => 'PaymentId',
            'RefundChargeOnCustomer' => false, 'ServiceChargeOnCustomer' => false,
            'Amount' => 2,
            'CurrencyIso' => 'KWD',
            'Comment' => 'test'
        ];

        foreach ($this->keys as $config) {
            try {

                $mfObj = new MyFatoorahRefund($config);
                $json = $mfObj->makeRefund($postFields);

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
