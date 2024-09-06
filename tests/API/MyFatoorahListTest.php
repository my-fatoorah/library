<?php

namespace MyFatoorah\Test\API;

use MyFatoorah\Library\API\MyFatoorahList;

class MyFatoorahListTest extends \PHPUnit\Framework\TestCase
{

    private $keys;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->keys = include __DIR__ . '/../apiKeys.php';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetCurrencyRates()
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahList($config);
                $json  = $mfObj->getCurrencyRates();

                $this->assertEquals('1.00000000', $json[0]->Value);
                $this->assertEquals('KWD', $json[0]->Text, $config['message']);
            } catch (\Exception $ex) {
                $exception = $config['getCurrencyRatesException'] ?? $config['exception'];
                $this->assertEquals($exception, $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
