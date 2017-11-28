<?php

namespace Tests\Feature;

use Slackbot001\CP_TDinstance;
use Tests\TestCase;

class CP_TDinstsanceTest extends TestCase
{
    /**
     * Tests TDinstance creation.
     *
     * @return void
     */
    public function testInstance()
    {
        $CPTD = new CP_TDinstance();
        $this->assertFalse($CPTD->checktoken());
    }
}
