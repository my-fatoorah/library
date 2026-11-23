<?php

namespace MyFatoorah\Test\V3;

use MyFatoorah\Test\MyFatoorahTestCase;
use MyFatoorah\Library\V3\MyFatoorahSessions;

class MyFatoorahSessionsTest extends MyFatoorahTestCase
{
    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function testCreateAndGetSessionDetails(): void
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

                $this->assertObjectHasProperty('SessionId', $data);
                $this->assertNotEmpty($data->SessionId);

                $details = $mfObj->getSessionDetails($data->SessionId);

                $this->assertObjectHasProperty('SessionExpiry', $details);

                // A session that has already expired is not a session the vendor can use.
                $this->assertGreaterThan(time(), strtotime($details->SessionExpiry));
            } catch (\Exception $ex) {
                $this->assertEquals($config['exception'], $ex->getMessage(), $config['message']);
            }
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
