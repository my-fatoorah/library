<?php

namespace MyFatoorah\Library\V3;

use MyFatoorah\Library\MyFatoorah;

class MyFatoorahSessions extends MyFatoorah
{
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Create a payment session.
     * POST /v3/sessions
     *
     * @param array $data the session request body
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
     * @param string $sessionId the MyFatoorah session id
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
