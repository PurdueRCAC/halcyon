<?php

namespace App\Modules\Queues\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\Queues\Models\Queue;

class QueueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Queue::class;

    /**
     * Factory definition
     *
     * @return  array
     */
    public function definition()
    {
        return [
            'schedulerid' => $this->faker->numberBetween(1, 5),
            'subresourceid' => $this->faker->numberBetween(1, 99),
            'name' => $this->faker->name,
            //'groupid' => $this->faker->randomNumber(),
            'queuetype' => $this->faker->numberBetween(1, 4),
            'automatic' => 0,
            'free' => 0,
            'schedulerpolicyid' => $this->faker->numberBetween(1, 5),
            'enabled' => 1,
            'started' => 1,
            'reservation' => 0,
            'cluster' => 'a',
            'priority' => 1000,
            'defaultwalltime' => $this->faker->numberBetween(1, 4),
            'maxjobsqueued' => 0,
            'maxjobsqueueduser' => 0,
            'maxjobsrun' => 0,
            'maxjobsrunuser' => 0,
            'maxjobcores' => 0,
            'nodecoresdefault' => 0,
            'nodecoresmin' => 8,
            'nodecoresmax' => 8,
            'nodememmin' => '16G',
            'nodememmax' => '16G',
            'aclusersenabled' => 1,
            'aclgroups' => '',
            'datetimecreated' => now(),
            'maxijobfactor' => 2,
            'maxijobuserfactor' => 1,
        ];
    }
}
