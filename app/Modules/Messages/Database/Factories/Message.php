<?php

/** @var \Illuminate\Database\Eloquent\Factories\Factory $factory */

use App\Modules\Messages\Models\Message;
use Faker\Generator as Faker;

$factory->define(Message::class, function (Faker $faker)
{
    return [
        'userid' => $faker->randomNumber(),
        'messagequeuetypeid' => $faker->numberBetween(1, 28),
        'targetobjectid' => $faker->randomNumber(),
        'messagequeueoptionsid' => 0,
        'pid' => $faker->randomNumber(),
        'datetimestarted' => now(),
        'returnstatus' => 0
    ];
});
