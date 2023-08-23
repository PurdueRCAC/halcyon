<?php

namespace App\Modules\Messages\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Messages\Models\Message;
use App\Modules\Messages\Models\Type;

class MessagesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = Type::all()->pluck('id');

        for ($i = 0; $i < 10; $i++)
        {
            Message::create([
                'userid' => rand(1, 999999999),
                'messagequeuetypeid' => $types->random(),
                'targetobjectid' => rand(1, 999999999),
            ]);
        }
    }
}
