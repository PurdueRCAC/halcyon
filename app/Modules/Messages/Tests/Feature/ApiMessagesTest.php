<?php

namespace App\Modules\Messages\Tests\Feature;

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
        $posts = Message::factory()->count(2)->make();

        foreach ($posts as $post)
        {
            $post->save();
        }

        $response = $this->json('get', route('api.messages.index'));

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

        $data = Message::factory()->make();

        $response = $this->actingAs($user)
            ->json('post', route('api.messages.create'), $data->toArray());

        $response
            ->assertStatus(201)
            ->assertJsonPath('targetobjectid', $data->targetobjectid)
            ->assertJsonPath('messagequeuetypeid', $data->messagequeuetypeid)
            ->assertJsonPath('userid', $data->userid);

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

        $data = Message::factory()->make();
        $data->id = null;
        $data->save();

        $response = $this->actingAs($user)
            ->json('get', route('api.messages.read', ['id' => $data->id]));

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $data->id)
            ->assertJsonPath('targetobjectid', $data->targetobjectid)
            ->assertJsonPath('messagequeuetypeid', $data->messagequeuetypeid);

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

        $data = Message::factory()->make();
        $data->id = null;
        $data->save();

        $put = array(
            'targetobjectid' => 2,
            'started' => 1
        );

        $response = $this->actingAs($user)
            ->json('put', route('api.messages.update', ['id' => $data->id]), $put);

        $response
            ->assertStatus(200)
            ->assertJsonPath('id', $data->id)
            ->assertJsonPath('targetobjectid', $put['targetobjectid']);

        $this->assertNotNull($response['datetimestarted']);

        $put = array(
            'returnstatus' => 1,
            'completed' => 1
        );

        $response = $this->actingAs($user)
            ->json('put', route('api.messages.update', ['id' => $data->id]), $put);

        $response
            ->assertJsonPath('returnstatus', $put['returnstatus']);

        $this->assertNotNull($response['datetimecompleted']);

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

        $data = Message::factory()->make();
        $data->id = null;
        $data->save();

        $response = $this->actingAs($user)
            ->json('delete', route('api.messages.delete', ['id' => $data->id]));

        $response->assertStatus(204);
    }
}
