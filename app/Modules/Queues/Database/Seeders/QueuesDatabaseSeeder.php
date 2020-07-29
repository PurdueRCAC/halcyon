<?php

namespace App\Modules\Queues\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QueuesDatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('queuetypes')->insert([
			'name' => 'compute queue'
		]);

		DB::table('queuetypes')->insert([
			'name' => 'storage'
		]);

		DB::table('queuetypes')->insert([
			'name' => 'virtual machine'
		]);

		DB::table('queuetypes')->insert([
			'name' => 'hadoop'
		]);
	}
}
