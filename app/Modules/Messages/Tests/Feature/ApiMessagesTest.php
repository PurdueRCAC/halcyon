<?php

namespace App\Modules\Messages\Tests\Api;

use App\Modules\Messages\Models\Message;
use Illuminate\Foundation\Testing\WithoutMiddleware;
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
        $posts = factory(Message::class, 2)->create();

        $response = $this->json('get', route('api.messages.index'));

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

        $data = factory(Message::class)->make();

        $response = $this->actingAs($user)
            ->json('post', route('api.messages.create'), $data->toArray());

        $response
            ->assertStatus(201)
            ->assertJsonPath('targetobjectid', $data->targetobjectid)
            ->assertJsonPath('messagequeuetypeid', $data->messagequeuetypeid)
            ->assertJsonPath('userid', $data->userid);
    }

    /**
     * Test the Read response
     *
     * @return void
     */
    public function testRead()
    {
        $user = new User;

        $data = factory(Message::class)->create();

        $response = $this->actingAs($user)
            ->json('get', route('api.messages.read', ['id' => $data->id]));

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $data->id)
            ->assertJsonPath('targetobjectid', $data->targetobjectid)
            ->assertJsonPath('messagequeuetypeid', $data->messagequeuetypeid);
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
            ->json('post', route('api.messages.create'), $fake->toArray());*/

        $put = array(
            'targetobjectid' => 2,
            'started' => 1
        );

        $response = $this->actingAs($user)
            ->json('put', route('api.messages.update', ['id' => $fake->id]), $put);

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $fake->id)
            ->assertJsonPath('targetobjectid', $put['targetobjectid']);
    }

    /**
     * Test the Delete response
     *
     * @return void
     */
    public function testDelete()
    {
        $user = new User;

        $fake = factory(Message::class)->create();

        $response = $this->actingAs($user)
            ->json('delete', route('api.messages.delete', ['id' => $fake->id]));

        $response->assertStatus(204);
    }
}
