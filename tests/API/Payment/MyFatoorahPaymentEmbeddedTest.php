<?php

namespace MyFatoorah\Test\API;

use MyFatoorah\Library\API\Payment\MyFatoorahPaymentEmbedded;
use PHPUnit\Framework\TestCase;

class MyFatoorahPaymentEmbeddedTest extends TestCase
{

    private $keys;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->keys = include __DIR__ . '/../../apiKeys.php';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetCheckoutGateways()
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
