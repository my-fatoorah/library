<?php

namespace MyFatoorah\Test\V3;

use MyFatoorah\Library\V3\MyFatoorahPayments;
use PHPUnit\Framework\TestCase;

class MyFatoorahPaymentsTest extends TestCase
{
    private $keys;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->keys = include __DIR__ . '/../apiKeys.php';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testCreatePayment()
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

                $this->assertIsObject($data);
                $this->assertObjectHasProperty('PaymentURL', $data);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetPaymentDetails()
    {
        $paymentId = '07076945628364902373';

        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahPayments($config);
                $data  = $mfObj->getPaymentDetails($paymentId);
                $this->assertIsObject($data);
                $this->assertObjectHasProperty('Invoice', $data);

                $this->assertEquals('PAID', $data->Invoice->Status);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
