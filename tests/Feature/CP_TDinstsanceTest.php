<?php

namespace Tests\Feature;

use Slackbot001\Classes\CP_TDinstance;
use Tests\TestCase;

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
        } else {
            $this->assertTrue(false);
        }
    }
}
