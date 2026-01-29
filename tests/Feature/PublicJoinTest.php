<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicJoinTest extends TestCase
{
    public function test_public_join_returns_payload(): void
    {
        $response = $this->get('/public/join/EVENT1/joindemo1234');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Public join stub',
            'eventCode' => 'EVENT1',
            'joinToken' => 'joindemo1234',
        ]);
    }
}
