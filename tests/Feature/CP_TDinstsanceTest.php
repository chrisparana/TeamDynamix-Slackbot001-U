<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Slackbot001\Classes\CP_TDinstance;

class CP_TDinstsanceTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testInstance()
    {
        $CPTD = new CP_TDinstance();
        if ($CPTD == 'FALSE') {
            $this->assertTrue(true);
        }
        else {
            $this->assertTrue(false);
        }
    }
}
