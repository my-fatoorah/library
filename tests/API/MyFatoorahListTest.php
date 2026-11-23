<?php

namespace MyFatoorah\Test\API;

use MyFatoorah\Test\MyFatoorahTestCase;
use MyFatoorah\Library\API\MyFatoorahList;

class MyFatoorahListTest extends MyFatoorahTestCase
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testGetCurrencyRates(): void
    {
        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahList($config);
                $json  = $mfObj->getCurrencyRates();

                $this->assertEquals('1.00000000', $json[0]->Value);
                $this->assertEquals('KWD', $json[0]->Text, $config['message']);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
