<?php

namespace App\Widgets\Helpform\Fields;

use Illuminate\Support\Str;
use App\Halcyon\Form\Fields\Select;
use App\Modules\Knowledge\Models\Page;

/**
 * Supports an article picker.
 */
class Articles extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Articles';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$topic = $this->value;
		// Initialise variables.
		$html     = array();
		$recordId = (int) $this->form->getValue('id');
		$size     = ($v = $this->element['size']) ? ' size="'.$v.'"' : '';
		$class    = ($v = $this->element['class']) ? ' class="form-control '.$v.'"' : 'class="form-control"';
		$value = '';

		$options = $this->getOptions();

		$k = 0;
		if (!empty($topic))
		{
			foreach ($topic as $k => $t)
			{
				$html[] = '<div class="topic-group">';
				$html[] = '<div class="form-group"><hr />';
					$html[] = '<label for="' . $this->id . '_' . $k . '_title">' . trans('widget.helpform::helpform.title') . '</label>';
					$html[] = '<input type="text" name="'.$this->name.'[' . $k . '][title]" id="' . $this->id . '_' . $k . '_title" value="' . htmlspecialchars($t['title'], ENT_COMPAT, 'UTF-8') . '"' . $size . $class . ' />';
				$html[] = '</div>';

				$html[] = '<div class="form-group">';
					$html[] = '<label for="' . $this->id . '_' . $k . '_article">' . trans('widget.helpform::helpform.article') . '</label>';
					$html[] = '<select name="'.$this->name.'[' . $k . '][article]" id="' . $this->id . '_' . $k . '_article"' . $size . $class . ' />';
					foreach ($options as $option)
					{
						$html[] = '<option value="' . $option['value'] . '"' . ($t['article'] == $option['value'] ? ' selected="selected"' : '') . '>' . $option['text'] . '</option>';
					}
					$html[] = '</select>';
				$html[] = '</div>';
				$html[] = '</div>';
			}
		}
		$k++;

		$html[] = '<div class="topic-group"><hr />';
			$html[] = '<div class="form-group">';
				$html[] = '<label for="' . $this->id . '_' . $k . '_title">' . trans('widget.helpform::helpform.title') . '</label>';
				$html[] = '<input type="text" name="'.$this->name.'[' . $k . '][title]" id="' . $this->id . '_' . $k . '_title" value=""' . $size . $class . ' />';
			$html[] = '</div>';

			$html[] = '<div class="form-group">';
				$html[] = '<label for="' . $this->id . '_' . $k . '_article">' . trans('widget.helpform::helpform.article') . '</label>';
				$html[] = '<select name="'.$this->name.'[' . $k . '][article]" id="' . $this->id . '_' . $k . '_article"' . $size . $class . ' />';
				foreach ($options as $option)
				{
					$html[] = '<option value="' . $option['value'] . '">' . $option['text'] . '</option>';
				}
				$html[] = '</select>';
			$html[] = '</div>';
		$html[] = '</div>';

		$html[] = '<div id="topic-template" class="d-none hide">';
			$html[] = '<div class="topic-group"><hr />';
			$html[] = '<div class="form-group">';
				$html[] = '<label for="' . $this->id . '_##_title">' . trans('widget.helpform::helpform.title') . '</label>';
				$html[] = '<input type="text" name="'.$this->name.'[##][title]" id="' . $this->id . '_##_title" value=""' . $size . $class . ' />';
			$html[] = '</div>';

			$html[] = '<div class="form-group">';
				$html[] = '<label for="' . $this->id . '_##_article">' . trans('widget.helpform::helpform.article') . '</label>';
				$html[] = '<select name="'.$this->name.'[##][article]" id="' . $this->id . '_##_article"' . $size . $class . ' />';
				foreach ($options as $option)
				{
					$html[] = '<option value="' . $option['value'] . '">' . $option['text'] . '</option>';
				}
				$html[] = '</select>';
			$html[] = '</div>';
			$html[] = '</div>';
		$html[] = '</div>';

		$html[] = '<div><button class="btn btn-success" id="topic-add">Add Topic</button></div>';
		$html[] = '<script>
		document.addEventListener("DOMContentLoaded", function () {
			document.getElementById("topic-add").addEventListener("click", function(e) {
				e.preventDefault();

				var template = document.getElementById("topic-template"),
					tmpl = template.innerHTML;
				tmpl = tmpl.replace(/##/g, document.querySelectorAll(".topic-group").length - 1);
				template.insertAdjacentHTML("beforebegin", tmpl);
			});
		});
		</script>';

		return implode("\n", $html);
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getOptions()
	{
		$rows = Page::tree();

		$options = array();

		$options[] = array(
			'value' => 0,
			'text' => trans('widget.helpform::helpform.select page')
		);

		foreach ($rows as $row)
		{
			$options[] = array(
				'value' => $row->id,
				'text' => str_repeat('|&mdash; ', $row->level) . e(Str::limit($row->title, 70))
			);
		}

		return $options;
	}
}
