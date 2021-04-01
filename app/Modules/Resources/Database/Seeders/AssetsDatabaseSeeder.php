<?php

namespace App\Modules\Resources\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Resources\Models\Asset;

class AssetsDatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Asset::create([
			'Cluster One',
			'rolename' => 'clusterone',
			'listname' => 'clusterone',
			'batchsystem' => 1,
			'resourcetype' => 1,
		]);

		Asset::create([
			'Cluster Two',
			'rolename' => 'clustertwo',
			'listname' => 'clustertwo',
			'batchsystem' => 4,
			'resourcetype' => 1,
		]);

		Asset::create([
			'Cluster Three',
			'rolename' => 'clusterthree',
			'listname' => 'clusterthree',
			'batchsystem' => 4,
			'resourcetype' => 1,
		]);

		Asset::create([
			'Cluster Four',
			'rolename' => 'clusterfour',
			'listname' => 'clusterfour',
			'batchsystem' => 4,
			'resourcetype' => 1,
		]);

		Asset::create([
			'Home',
			'rolename' => '',
			'listname' => 'home',
			'resourcetype' => 2,
		]);

		Asset::create([
			'Scratch',
			'rolename' => '',
			'listname' => 'scratch',
			'resourcetype' => 2,
		]);
	}
}
