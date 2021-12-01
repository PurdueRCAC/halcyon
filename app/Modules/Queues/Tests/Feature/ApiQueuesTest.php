<?php

namespace App\Modules\Queues\Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Users\Models\User;
use Tests\TestCase;

class ApiQueuesTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test the Index (default) response
     *
     * @return void
     */
    public function testIndex()
    {
        $posts = Queue::factory()->count(2)->make();

        foreach ($posts as $post)
        {
            $post->save();
        }

        $response = $this->json('get', route('api.queues.index'));

        $response
            ->assertStatus(200);
            //->assertJsonPath('data', $posts->toArray());

        foreach ($posts as $post)
        {
            $post->delete();
        }
    }

    /**
     * Test the Create response
     *
     * @return void
     */
    public function testCreate()
    {
        $user = new User;

        $data = Queue::factory()->make();
        $data->schedulerid = Scheduler::query()->limit(1)->first()->id;

        $response = $this->actingAs($user)
            ->json('post', route('api.queues.create'), $data->toArray());

        $response
            ->assertStatus(201)
            ->assertJsonPath('schedulerid', $data->schedulerid)
            ->assertJsonPath('subresourceid', $data->subresourceid)
            ->assertJsonPath('name', $data->name)
            ->assertJsonPath('groupid', $data->groupid);

        $data->id = $response->decodeResponseJson()->json('id');
        $data->delete();
    }

    /**
     * Test the Read response
     *
     * @return void
     */
    public function testRead()
    {
        $user = new User;

        $data = Queue::factory()->make();
        $data->id = null;
        $data->save();

        $response = $this->actingAs($user)
            ->json('get', route('api.queues.read', ['id' => $data->id]));

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $data->id)
            ->assertJsonPath('schedulerid', $data->schedulerid)
            ->assertJsonPath('subresourceid', $data->subresourceid)
            ->assertJsonPath('name', $data->name)
            ->assertJsonPath('groupid', $data->groupid);

        $data->delete();
    }

    /**
     * Test the Update response
     *
     * @return void
     */
    public function testUpdate()
    {
        $user = new User;

        $data = Queue::factory()->make();
        $data->id = null;
        $data->save();

        $put = array(
            'schedulerid' => 2,
            'started' => 0
        );

        $response = $this->actingAs($user)
            ->json('put', route('api.queues.update', ['id' => $data->id]), $put);

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $data->id)
            ->assertJsonPath('schedulerid', $put['schedulerid'])
            ->assertJsonPath('started', $put['started']);

        $data->delete();
    }

    /**
     * Test the Delete response
     *
     * @return void
     */
    public function testDelete()
    {
        $user = new User;

        $data = Queue::factory()->make();
        $data->id = null;
        $data->save();

        $response = $this->actingAs($user)
            ->json('delete', route('api.queues.delete', ['id' => $data->id]));

        $response->assertStatus(204);
    }
}
