<?php

namespace MyFatoorah\Test;

use MyFatoorah\Library\ShippingMyfatoorahApiV2;

class ShippingMyfatoorahApiV2Test extends \PHPUnit\Framework\TestCase {

    private $keys;

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct() {
        parent::__construct();
        $this->keys = include ('apiKeys.php');
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetShippingCountries() {
        foreach ($this->keys as $token) {
            try {
                $mfObj = new ShippingMyfatoorahApiV2($token['apiKey'], $token['countryMode'], $token['isTest']);
                $json  = $mfObj->getShippingCountries();

                $this->assertEquals('AD', $json->Data[0]->CountryCode);
                $this->assertEquals('ANDORRA', $json->Data[0]->CountryName);
            } catch (\Exception $ex) {
                $this->assertEquals($token['exception'], $ex->getMessage(), $token['message']);
            }
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetShippingCities() {
        foreach ($this->keys as $token) {
            try {
                $mfObj = new ShippingMyfatoorahApiV2($token['apiKey'], $token['countryMode'], $token['isTest']);
                $json  = $mfObj->getShippingCities(1, 'KW', 'ada');

                $this->assertEquals('KW', $json->Data->CountryCode);
                $this->assertEquals('ADAN', $json->Data->CityNames[0]);
                $this->assertEquals('SHUHADA', $json->Data->CityNames[1]);
            } catch (\Exception $ex) {
                $this->assertEquals($token['exception'], $ex->getMessage(), $token['message']);
            }
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function testCalculateShippingCharge() {
        $mfObj = new ShippingMyfatoorahApiV2($this->keys['valid']['apiKey'], $this->keys['valid']['countryMode'], $this->keys['valid']['isTest']);

        $shippingData = [
            'ShippingMethod' => 1,
            'Items'          => [[
            'ProductName' => 'product',
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

        $json = $mfObj->calculateShippingCharge($shippingData);
        $this->assertEquals('KD', $json->Data->Currency);
        $this->assertEquals(45.926, $json->Data->Fees);
    }

    public function testCalculateShippingChargeExceptionProductName() {
        $mfObj = new ShippingMyfatoorahApiV2($this->keys['valid']['apiKey'], $this->keys['valid']['countryMode'], $this->keys['valid']['isTest']);

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
