<?php
namespace App\Widgets\Impacttables;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\Impact\Models\Table;
use App\Modules\Impact\Models\Impact;
use App\Modules\Impact\Models\AwardReport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Display impact data
 */
class Impacttables extends Widget
{
	/**
	 * Display
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$it = (new Table)->getTable();
		$i  = (new Impact)->getTable();

		$all_rows = app('db')
			->table($it)
			->join($i, $i . '.impacttableid', $it . '.id')
			->select(
				$it . '.name AS name',
				$i . '.name AS rowname',
				$it . '.columnname as columnname',
				$i . '.value as value',
				$it . '.sequence AS itsequence',
				$i . '.sequence AS isequence',
				DB::raw('MAX(' . $i . '.updatedatetime) AS updated')
			)
			->where($i . '.name', '!=', '')
			->where($i . '.value', '!=', '')
			->groupBy($it . '.sequence')
			->groupBy($i . '.sequence')
			->groupBy($it . '.name')
			->groupBy($i . '.name')
			->groupBy($it . '.columnname')
			->groupBy($i . '.value')
			->orderBy($it . '.sequence', 'asc')
			->orderBy($i . '.sequence', 'asc')
			->get();

		$data = AwardReport::query()
			->where('awardeecount', '!=', 0)
			->orderBy('fiscalyear', 'desc')
			->get();

		$updatedatetime = Impact::query()
			->select(DB::raw('MAX(updatedatetime) AS updated'))
			->first();

		$updatedatetime = Carbon::parse($updatedatetime->updated);

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'all_rows' => $all_rows->toArray(),
			'data' => $data,
			'updatedatetime' => $updatedatetime
		]);
	}
}
