<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Modules\Core\Models\Extension;
use App\Halcyon\Access\Asset;

class RegisterQueuesModule extends Migration
{
	/**
	 * Module name
	 * 
	 * @var  string
	 */
	public $module = 'queues';

	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('extensions'))
		{
			$exist = Extension::findModuleByName($this->module);

			if (!$exist)
			{
				Extension::create([
					'element' => $this->module,
					'type' => 'module',
					'enabled' => 1,
					'access' => 1,
					'protected' => 1,
					'name' => trans('queues::queues.module name')
				]);
			}
		}

		if (Schema::hasTable('permissions'))
		{
			// Secondly, add asset entry if not yet created
			$exist = Asset::findByName($this->module);

			if (!$exist)
			{
				// Build default ruleset
				$defaulRules = array();
					/*'admin'      => array(
						'7' => 1
					),
					'manage'     => array(
						'6' => 1
					),
					'create'     => array(),
					'delete'     => array(),
					'edit'       => array(),
					'edit.state' => array()
				);*/

				// Register the module just under root in the assets table
				$asset = new Asset;
				$asset->name = $this->module;
				$asset->parent_id = 1;
				$asset->rules = json_encode($defaulRules);
				$asset->title = $this->module;
				$asset->save(); //AsChildOf(1);
			}
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		$exist = Extension::findModuleByName($this->module);

		if ($exist)
		{
			$exist->delete();
		}

		$exist = Asset::findByName($this->module);

		if ($exist)
		{
			$exist->delete();
		}
	}
}
