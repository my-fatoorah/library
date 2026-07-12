<?php

namespace MyFatoorah\Library\V3;

use MyFatoorah\Library\MyFatoorah;

/**
 * Handles v3 session APIs.
 *
 * @author    MyFatoorah <tech@myfatoorah.com>
 * @copyright MyFatoorah, All rights reserved
 * @license   GNU General Public License v3.0
 */
class MyFatoorahSessions extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Create a payment session.
     * POST /v3/sessions
     *
     * @param array $data
     *
     * @return object
     */
    public function createSession(array $data)
    {
        $json = $this->callAPI($this->apiURL . '/v3/sessions', $data, null, 'CreateSession');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get session details by sessionId.
     * GET /v3/sessions/{sessionId}
     *
     * @param string $sessionId
     *
     * @return object
     */
    public function getSessionDetails($sessionId)
    {
        $json = $this->callAPI($this->apiURL . '/v3/sessions/' . $sessionId, null, $sessionId, 'GetSessionDetails');
        return $json->Data;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
