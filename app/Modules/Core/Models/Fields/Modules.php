<?php

namespace App\Modules\Core\Models\Fields;

use App\Modules\Core\Models\Extension;
use App\Halcyon\Form\Field;

/**
 * Form Field class
 */
class Modules extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Modules';

	/**
	 * Method to get the list of menus for the field options.
	 *
	 * @return  string
	 */
	protected function getInput()
	{
		/*$items = Extension::query()
			->where('type', '=', 'module')
			->where('enabled', '=', 1)
			->orderBy('folder', 'asc')
			->orderBy('name', 'asc')
			->get();*/
		$values = $this->value;
		//echo '<pre>';
		//print_r($values); echo '</pre>';die();
		$values = !is_array($values) ? array() : $values;

		$groupings = [
			'dashboard' => ['dashboard'],
			'system' => ['core', 'history', 'config'],
			'users' => ['users'],
			//'menus' => ['menus'],
			'content' => ['pages', 'tags', 'media'],
			'extensions' => ['widgets', 'listeners'],
			'themes' => ['themes'],
		];

		$protected = array('menus');
		foreach ($groupings as $grouping => $mods)
		{
			$protected = array_merge($protected, $mods);
		}

		$items = Extension::query()
			->where('type', '=', 'module')
			->where('enabled', '=', 1)
			->whereNotIn('element', $protected)
			->orderBy('name', 'asc')
			->get();

		$html = '<fieldset>';
		$i = 0;
		foreach ($groupings as $grouping => $mods)
		{
			$value = array(
				'grouping' => $grouping,
				'class' => $grouping,
				//'modules' => array(),
			);

			foreach ($values as $l => $val)
			{
				if (isset($val['grouping']) && $val['grouping'] == $grouping)
				{
					$value['grouping'] = $val['grouping'];
					$value['class'] = isset($val['class']) ? $val['class'] : $val['grouping'];
					//$value['modules'] = isset($val['modules']) ? $val['modules'] : array();
					unset($values[$l]);
				}
			}
		$html .= '
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Grouping</label>
								<input type="text" name="' . $this->name . '[' . $i . '][grouping]" class="form-control" readonly value="' . e($value['grouping']) . '" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>CSS class</label>
								<input type="text" name="' . $this->name . '[' . $i . '][class]" class="form-control" value="' . e($value['class']) . '" />
							</div>
						</div>
					</div>';
					/*$html .= '<ul class="mb-2">';
					foreach ($mods as $j => $mod)
					{
						$html .= '<li class="grouping-module">
								<span class="fa fa-lock" aria-hidden="true"></span> ' . trans($mod . '::' . $mod . '.module name') . '
							</li>';
					}
					$html .= '</ul><div class="form-group grouping-module">';
					$html .= '<select name="' . $this->name . '[' . $i . '][modules][]" multiple size="8" class="form-control">';
					foreach ($items as $item)
					{
						if (in_array($item->element, $protected))
						{
							$continue;
						}
						$html .= '<option value="' . e($item->element) . '"' . (in_array($item->element, $value['modules']) ? ' selected' : '') . '>' . trans($item->name . '::' . $item->name . '.module name') . '</option>';
					}
					$html .= '</select>'; //<div class="input-group-append"><button class="input-group-text text-danger"><span class="fa fa-trash" aria-hidden="true"></span></button></div></div>
					$html .= '</div>';*/
					$html .= '
			';
			$i++;
		}

		if (count($values) < 3)
		{
			for ($z = count($values); $z < 3; $z++)
			{
				$values[] = array(
					'grouping' => '',
					'class' => '',
					//'modules' => array(),
				);
			}
		}

		foreach ($values as $value)
		{
			if (!isset($value['grouping']))
			{
				$value['grouping'] = '';
			}
			if (!isset($value['class']))
			{
				$value['class'] = $value['grouping'];
			}
			/*if (!isset($value['modules']))
			{
				$value['modules'] = array();
			}*/

			$html .= '
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Grouping</label>
								<input type="text" name="' . $this->name . '[' . $i . '][grouping]" class="form-control" value="' . e($value['grouping']) . '" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>CSS class</label>
								<input type="text" name="' . $this->name . '[' . $i . '][class]" class="form-control" value="' . e($value['class']) . '" />
							</div>
						</div>
					</div>';
					/*$html .= '<div class="form-group grouping-module">';
					$html .= '<select name="' . $this->name . '[' . $i . '][modules][]" multiple size="8" class="form-control">';
					foreach ($items as $item)
					{
						if (in_array($item->element, $protected))
						{
							$continue;
						}
						$html .= '<option value="' . e($item->element) . '"' . (in_array($item->element, $value['modules']) ? ' selected' : '') . '>' . trans($item->name . '::' . $item->name . '.module name') . '</option>';
					}
					$html .= '</select>'; //<div class="input-group-append"><button class="input-group-text text-danger"><span class="fa fa-trash" aria-hidden="true"></span></button></div></div>
					$html .= '</div>';*/
					$html .= '
			';
			$i++;
		}

		/*$html .= '<fieldset class="grouping d-none" id="new-grouping">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Grouping</label>
								<input type="text" name="grouping[{i}][\'label\']" class="form-control" disabled value="" />
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>CSS class</label>
								<input type="text" name="grouping[{i}][\'class\']" class="form-control" value="" />
							</div>
						</div>
					</div>
							<div class="form-group grouping-module">
							<div class="input-group"><select name="grouping[{i}][\'modules\'][]" class="form-control">
						<option valut="">- Select module -</option>';
					foreach ($items as $item)
					{
						if (in_array($item->element, $protected))
						{
							$continue;
						}
						$html .= '<option value="' . e($item->element) . '">' . e($item->name) . '</option>';
					}
					$html .= '</select><div class="input-group-append"><button class="input-group-text text-danger"><span class="fa fa-trash" aria-hidden="true"></span></button></div></div>
							</div>
			</fieldset>
			<script>
			document.addEventListener("DOMContentLoaded", function () {
				document.getElementById("add-grouping").addEventListener("click", function(e) {
					e.preventDefault();

					var template = document.getElementById(this.getAttribute("data-template")).innerHTML;
					var groupings = document.querySelectorAll(".grouping");

					template.replace("{i}", groupings.length);

					this.parentNode.insertBefore(template, this);
				});
				document.querySelectorAll(".select-module").forEach(function(el) {
					el.addEventListener("change", function(e) {

					});
				});
			});
			</script>';

		$html .= '<button id="add-grouping" class="btn btn-secondary" data-template="new-grouping">Add Grouping</button>';*/
		$html .= '</fieldset>';

		return $html;
	}
}
