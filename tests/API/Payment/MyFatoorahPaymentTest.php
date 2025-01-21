<?php

namespace MyFatoorah\Test\API\Payment;

use MyFatoorah\Library\API\Payment\MyFatoorahPayment;

class MyFatoorahPaymentTest extends \PHPUnit\Framework\TestCase
{

    private $keys;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->keys = include __DIR__ . '/../../apiKeys.php';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testInitiatePayment()
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahPayment($config);
                $json  = $mfObj->initiatePayment();

                $this->assertArrayHasKey('PaymentMethodId', (array) $json[0]);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testSendPayment()
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj    = new MyFatoorahPayment($config);
                $curlData = [
                    'CustomerName'       => 'test: test',
                    'InvoiceValue'       => '729',
                    'DisplayCurrencyIso' => 'SAR',
                    'MobileCountryCode'  => '053',
                    'CustomerMobile'     => '131421335',
                    'CustomerEmail'      => 'test@test.com',
                    'Language'           => 'ar',
                    'CustomerReference'  => '1000851453',
                    'CustomerCivilId'    => null,
                    'UserDefinedField'   => 'CK-1',
                    'CustomerAddress'    => null,
                    'ShippingConsignee'  => '',
                    'ShippingMethod'     => null,
                    'InvoiceItems'       => [[
                    "ItemName"  => "<span>string</span>",
                    "Quantity"  => 1,
                    "UnitPrice" => 729,
                    "Weight"    => 0,
                    "Width"     => 0,
                    "Height"    => 0,
                    "Depth"     => 0
                        ]],
                    'NotificationOption' => 'LNK'
                ];
                $json     = $mfObj->sendPayment($curlData);

                $this->assertArrayHasKey('InvoiceURL', (array) $json);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * change the accessibility of a function
     * usage $method->invokeArgs($mfObj, [$ua]);
     *
     * @param  type $name
     * @return type
     */
    //    protected static function getMethod($name) {
    //        $class  = new \ReflectionClass('\MyFatoorah\Library\MyfatoorahPayment');
    //        $method = $class->getMethod($name);
    //        $method->setAccessible(true);
    //        return $method;
    //    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
}
