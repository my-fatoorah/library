<?php

namespace MyFatoorah\Test\V3;

use MyFatoorah\Library\V3\MyFatoorahSessions;
use PHPUnit\Framework\TestCase;

class MyFatoorahSessionsTest extends TestCase
{
    private $keys;

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        parent::__construct();
        $this->keys = include __DIR__ . '/../apiKeys.php';
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testCreateAndGetSessionDetails()
    {
        $curlData = [
            'Order' => [
                'Amount'      => 10,
                'CurrencyIso' => 'KWD',
            ],
        ];

        foreach ($this->keys as $config) {
            try {
                $mfObj = new MyFatoorahSessions($config);
                $data  = $mfObj->createSession($curlData);

                $this->assertIsObject($data);
                $this->assertObjectHasProperty('SessionId', $data);

                $details = $mfObj->getSessionDetails($data->SessionId);

                $this->assertIsObject($details);
                $this->assertObjectHasProperty('SessionExpiry', $details);
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
