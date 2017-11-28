<?php

namespace Tests\Feature;

use Tests\TestCase;

class frontTest extends TestCase
{
    /**
     * Tests SessionManager.
     *
     * @return void
     */
    public function testSession()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
