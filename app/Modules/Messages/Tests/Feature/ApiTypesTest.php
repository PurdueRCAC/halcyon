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
        $response = $this->json('get', route('api.messages.types'));

        $response
            ->assertStatus(200);
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
            'name' => 'feature test',
            'resourceid' => 1,
            'classname' => 'storagedir'
        );

        $response = $this->actingAs($user)
            ->json('post', route('api.messages.types.create'), $data);

        $response
            ->assertStatus(201)
            ->assertJsonPath('name', $data['name'])
            ->assertJsonPath('resourceid', $data['resourceid'])
            ->assertJsonPath('classname', $data['classname']);
    }

    /**
     * Test the Read response
     *
     * @return void
     */
    public function testRead()
    {
        $user = new User;

        $data = array(
            'name' => 'feature test',
            'resourceid' => 1,
            'classname' => 'storagedir'
        );

        $response = $this->actingAs($user)
            ->json('post', route('api.messages.types.create'), $data);

        $created = $response->decodeResponseJson();

        $response = $this->actingAs($user)
            ->json('get', route('api.messages.types.read', ['id' => $created['id']]));

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $created['id'])
            ->assertJsonPath('name', $data['name'])
            ->assertJsonPath('resourceid', $data['resourceid'])
            ->assertJsonPath('classname', $data['classname']);
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
            'name' => 'feature test',
            'resourceid' => 1,
            'classname' => 'storagedir'
        );

        $response = $this->actingAs($user)
            ->json('post', route('api.messages.types.create'), $data);

        $fake = $response->decodeResponseJson();

        $put = array(
            'name' => 'feature update test'
        );

        $response = $this->actingAs($user)
            ->json('put', route('api.messages.types.update', ['id' => $fake['id']]), $put);

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $fake['id'])
            ->assertJsonPath('name', $put['name']);
    }

    /**
     * Test the Delete response
     *
     * @return void
     */
    public function testDelete()
    {
        $user = new User;

        $data = array(
            'name' => 'feature test',
            'resourceid' => 1,
            'classname' => 'storagedir'
        );

        $response = $this->actingAs($user)
            ->json('post', route('api.messages.types.create'), $data);

        $fake = $response->decodeResponseJson();

        $response = $this->actingAs($user)
            ->json('delete', route('api.messages.types.delete', ['id' => $fake['id']]));

        $response->assertStatus(204);
    }
}
