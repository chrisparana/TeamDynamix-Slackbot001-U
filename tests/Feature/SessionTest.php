<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Slackbot001\SessionManager;

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
