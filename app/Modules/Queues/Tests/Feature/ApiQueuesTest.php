<?php

namespace App\Modules\Queues\Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Modules\Queues\Models\Queue;
use App\Modules\Users\Models\User;
use Tests\TestCase;

class ApiMessagesTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test the Index (default) response
     *
     * @return void
     */
    public function testIndex()
    {
        $posts = factory(Queue::class, 2)->create();

        $response = $this->json('get', route('api.queues.index'));

        $response
            ->assertStatus(200);
            //->assertJsonPath('data', $posts->toArray());
    }

    /**
     * Test the Create response
     *
     * @return void
     */
    public function testCreate()
    {
        $user = new User;

        $data = factory(Queue::class)->make();

        $response = $this->actingAs($user)
            ->json('post', route('api.queues.create'), $data->toArray());

        $response
            ->assertStatus(201)
            ->assertJsonPath('schedulerid', $data->schedulerid)
            ->assertJsonPath('subresourceid', $data->subresourceid)
            ->assertJsonPath('name', $data->name)
            ->assertJsonPath('groupid', $data->groupid);
    }

    /**
     * Test the Read response
     *
     * @return void
     */
    public function testRead()
    {
        $user = new User;

        $data = factory(Queue::class)->create();

        $response = $this->actingAs($user)
            ->json('get', route('api.queues.read', ['id' => $data->id]));

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $data->id)
            ->assertJsonPath('schedulerid', $data->schedulerid)
            ->assertJsonPath('subresourceid', $data->subresourceid)
            ->assertJsonPath('name', $data->name)
            ->assertJsonPath('groupid', $data->groupid);
    }

    /**
     * Test the Update response
     *
     * @return void
     */
    public function testUpdate()
    {
        $user = new User;

        $fake = factory(Message::class)->create();

        /*$response = $this->actingAs($user)
            ->json('post', route('api.queues.create'), $fake->toArray());*/

        $put = array(
            'schedulerid' => 2,
            'started' => 1
        );

        $response = $this->actingAs($user)
            ->json('put', route('api.queues.update', ['id' => $fake->id]), $put);

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $fake->id)
            ->assertJsonPath('schedulerid', $put['schedulerid']);
    }

    /**
     * Test the Delete response
     *
     * @return void
     */
    public function testDelete()
    {
        $user = new User;

        $fake = factory(Queue::class)->create();

        $response = $this->actingAs($user)
            ->json('delete', route('api.queues.delete', ['id' => $fake->id]));

        $response->assertStatus(204);
    }
}
