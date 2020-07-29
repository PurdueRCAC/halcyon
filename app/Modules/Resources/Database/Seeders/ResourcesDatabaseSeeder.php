<?php

namespace App\Modules\Resources\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourcesDatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('resourcetypes')->insert([
			'name' => 'Compute'
		]);

		DB::table('resourcetypes')->insert([
			'name' => 'Storage'
		]);
	}
}
