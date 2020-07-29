<?php

namespace App\Modules\Resources\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StorageDatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('storagedirquotanotificationtypes')->insert([
			'name' => 'Usage Report',
			'defaulttimeperiodid' => 2,
			'valuetype' => 1
		]);

		DB::table('storagedirquotanotificationtypes')->insert([
			'name' => 'Space Threshold - Value',
			'defaulttimeperiodid' => 0,
			'valuetype' => 2
		]);

		DB::table('storagedirquotanotificationtypes')->insert([
			'name' => 'Space Threshold - Percent',
			'defaulttimeperiodid' => 0,
			'valuetype' => 3
		]);

		DB::table('storagedirquotanotificationtypes')->insert([
			'name' => 'File Threshold - Value',
			'defaulttimeperiodid' => 0,
			'valuetype' => 4
		]);

		DB::table('storagedirquotanotificationtypes')->insert([
			'name' => 'File Threshold - Percent',
			'defaulttimeperiodid' => 0,
			'valuetype' => 5
		]);
	}
}
