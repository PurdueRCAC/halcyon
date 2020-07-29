<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Modules\Core\Models\Extension;

class RegisterQueuesModule extends Migration
{
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

				$this->info(sprintf('Added extension entry for module "%s"', $this->module));
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

				// Register the component container just under root in the assets table
				$asset = new Asset;
				$asset->name = $module;
				$asset->parent_id = 1;
				$asset->rules = json_encode($defaulRules);
				$asset->title = $this->module;
				$asset->save(); //AsChildOf(1);

				$this->info(sprintf('Added permissions entry for module "%s"', $this->module));
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
			$this->info(sprintf('Deleted extensions entry for module "%s"', $this->module));
		}

		$exist = Asset::findByName($this->module);

		if ($exist)
		{
			$exist->delete();
			$this->info(sprintf('Deleted permissions entry for module "%s"', $this->module));
		}
	}
}
