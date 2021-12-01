<?php

namespace App\Modules\Messages\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Messages\Models\Message;

class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Factory definition
     *
     * @return  array
     */
    public function definition()
    {
        return [
            'userid' => $this->faker->randomNumber(),
            'messagequeuetypeid' => $this->faker->numberBetween(1, 28),
            'targetobjectid' => $this->faker->randomNumber(),
            'messagequeueoptionsid' => 0,
            'pid' => $this->faker->randomNumber(),
            'datetimestarted' => now(),
            'returnstatus' => 0
        ];
    }
}
