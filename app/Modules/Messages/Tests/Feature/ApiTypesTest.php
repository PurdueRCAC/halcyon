<?php

namespace App\Modules\Messages\Tests\Api;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Modules\Users\Models\User;
use Tests\TestCase;

class ApiTypesTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test the Index (default) response
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->json('get', '/api/messages/types');

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
            'name' => 'foo',
        );

        $response = $this->actingAs($user)
            ->json('post', '/api/messages/types', $data)
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
        $response = $this->json('get', '/api/messages/types/1');

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
            'name' => 'bar',
        );

        $response = $this->actingAs($user)
            ->json('put', '/api/messages/types/1', $data)
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
            ->json('delete', '/api/messages/types/1');

        $response->assertStatus(200);
    }
}
