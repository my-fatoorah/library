<?php

namespace MyFatoorah\Test\API;

use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
use PHPUnit\Framework\TestCase;

class MyFatoorahPaymentStatusTest extends TestCase
{

    private $keys;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->keys = include __DIR__ . '/../../apiKeys.php';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetPaymentStatus()
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahPaymentStatus($config);
                $data  = $mfObj->getPaymentStatus('100202312116138082', 'PaymentId');

                $this->assertEquals('Paid', $data->InvoiceStatus);
            } catch (\Exception $ex) {
                $exception = $config['getPaymentStatusException'] ?? $config['exception'];
                $this->assertEquals($exception, $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
