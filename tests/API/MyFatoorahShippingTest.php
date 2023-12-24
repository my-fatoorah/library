<?php

namespace MyFatoorah\Test\API;

use MyFatoorah\Library\API\MyFatoorahShipping;

class MyFatoorahShippingTest extends \PHPUnit\Framework\TestCase
{

    private $keys;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->keys = include __DIR__ . '/../apiKeys.php';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetShippingCountries()
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahShipping($config);
                $data  = $mfObj->getShippingCountries();

                $this->assertEquals('AD', $data[0]->CountryCode);
                $this->assertEquals('ANDORRA', $data[0]->CountryName);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetShippingCities()
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj  = new MyfatoorahShipping($config);
                $cities = $mfObj->getShippingCities(1, 'KW', 'ada');

                $this->assertEquals('ADAN', $cities[0]);
                $this->assertEquals('SHUHADA', $cities[1]);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testCalculateShippingCharge()
    {
        $mfObj = new MyfatoorahShipping($this->keys['valid']);

        $shippingData = [
            'ShippingMethod' => 1,
            'Items'          => [
                [
                    'ProductName' => 'product',
                    'Description' => 'product',
                    'Weight'      => 10,
                    'Width'       => 10,
                    'Height'      => 10,
                    'Depth'       => 10,
                    'Quantity'    => 1,
                    'UnitPrice'   => '17.234',
                ]
            ],
            'CountryCode'    => 'KW',
            'CityName'       => 'adan',
            'PostalCode'     => '12345',
        ];

        $data = $mfObj->calculateShippingCharge($shippingData);
        $this->assertEquals('KD', $data->Currency);
    }

    public function testCalculateShippingChargeExceptionProductName()
    {
        $mfObj = new MyfatoorahShipping($this->keys['valid']);

        //test empty ProductName
        $shippingData1 = [
            'ShippingMethod' => 1,
            'Items'          => [[
            'ProductName' => '',
            'Description' => 'product',
            'Weight'      => 10,
            'Width'       => 10,
            'Height'      => 10,
            'Depth'       => 10,
            'Quantity'    => 1,
            'UnitPrice'   => '17.234',
                ]],
            'CountryCode'    => 'KW',
            'CityName'       => 'adan',
            'PostalCode'     => '12345',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('model.Items[0].ProductName: The field Product Name (En) is mandatory.');
        $mfObj->calculateShippingCharge($shippingData1);
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
