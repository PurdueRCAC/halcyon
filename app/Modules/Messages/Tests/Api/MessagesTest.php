<?php

namespace App\Modules\Messages\Tests\Api;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Modules\Users\Models\User;
use Tests\TestCase;

class MessagesTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test the Index (default) response
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get('/api/messages');

        $response->assertStatus(200);
    }

    /**
     * Test the Create response
     *
     * @return void
     */
    public function testCreate()
    {
        $user = new User;

        $data = array(
            'targetid' => 1,
        );

        $response = $this->actingAs($user)
            ->post('/api/messages', $data)
            ->seeJsonEquals([
                'id' => true
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test the Read response
     *
     * @return void
     */
    public function testRead()
    {
        $response = $this->get('/api/messages/1');

        $response->assertStatus(200);
    }

    /**
     * Test the Update response
     *
     * @return void
     */
    public function testUpdate()
    {
        $user = new User;

        $data = array(
            'targetid' => 2,
        );

        $response = $this->actingAs($user)
            ->put('/api/messages/1', $data)
            ->seeJsonEquals([
                'id' => true
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test the Delete response
     *
     * @return void
     */
    public function testDelete()
    {
        $user = new User;

        $response = $this->actingAs($user)
            ->delete('/api/messages/1');

        $response->assertStatus(200);
    }
}
