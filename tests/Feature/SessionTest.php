<?php

namespace Tests\Feature;

use Slackbot001\SessionManager;
use Tests\TestCase;

class SessionTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSession()
    {
        $userSession = new SessionManager();
        $TDinstance = $userSession->setupSession('testID', 'testToken');
        $this->assertTrue($userSession->checkSession('testID'));
        $TDinstance = $userSession->setupSession('testID', 'testToken');
        $this->assertFalse($TDinstance->checktoken());
        $userSession->deleteSession('testID');
        $this->assertFalse($userSession->checkSession('testID'));
    }
}
