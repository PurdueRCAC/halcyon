<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Form\Field;
use App\Halcyon\Html\Builder\Behavior;
use App\Halcyon\Html\Builder\Asset;

/**
 * Provides a modal media selector including upload mechanism
 */
class Media extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Media';

	/**
	 * The initialised state of the document object.
	 *
	 * @var  boolean
	 */
	protected static $initialised = false;

	/**
	 * Method to get the field input markup for a media selector.
	 * Use attributes to identify specific created_by and asset_id fields
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$assetField  = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];

		if ($asset == '')
		{
			$asset = app('request')->segments(1);
		}

		$link = (string) $this->element['link'];

		if (!self::$initialised)
		{
			// Load the modal behavior script.
			//Behavior::modal();

			// Build the script.
			$script = array();
			$script[] = '	function jInsertFieldValue(value, id) {';
			$script[] = '		var old_value = $("#" + id).val();';
			$script[] = '		if (old_value != value) {';
			$script[] = '			var elem = $("#" + id);';
			$script[] = '			elem.val(value);';
			$script[] = '			elem.trigger("change");';
			$script[] = '			if (typeof(elem.onchange) === "function") {';
			$script[] = '				elem.onchange();';
			$script[] = '			}';
			$script[] = '			jMediaRefreshPreview(id);';
			$script[] = '		}';
			$script[] = '	}';

			$script[] = '	function jMediaRefreshPreview(id) {';
			$script[] = '		id = "#" + id;';
			$script[] = '		var value = $(id).val();';
			$script[] = '		var img = $(id + "_preview");';
			$script[] = '		if (img) {';
			$script[] = '			if (value) {';
			$script[] = '				img.src = "' . url('/') . '" + value;';
			$script[] = '				$(id + "_preview_empty").css("display", "none");';
			$script[] = '				$(id + "_preview_img").css("display", "");';
			$script[] = '			} else { ';
			$script[] = '				img.src = ""';
			$script[] = '				$(id + "_preview_empty").css("display", "");';
			$script[] = '				$(id + "_preview_img").css("display", "none");';
			$script[] = '			} ';
			$script[] = '		} ';
			$script[] = '	}';

			$script[] = '	function jMediaRefreshPreviewTip(tip)';
			$script[] = '	{';
			$script[] = '		$(tip).css("display", "block");';
			$script[] = '		var img = tip.find("img.media-preview");';
			$script[] = '		var id = $(img).attr("id");';
			$script[] = '		id = id.substring(0, id.length - "_preview".length);';
			$script[] = '		jMediaRefreshPreview(id);';
			$script[] = '	}';

			// Add the script to the document head.
			//app('document')->addScriptDeclaration(implode("\n", $script));

			self::$initialised = true;
		}

		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// The text field.
		$html[] = '<div class="input-modal">';
		$html[] = '	<span class="input-cell">';
		$html[] = '	<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . ' readonly="readonly"' . $attr . ' />';
		$html[] = '	</span>';

		$directory = (string) $this->element['directory'];
		if ($this->value && file_exists(storage_path() . '/' . $this->value))
		{
			$folder = explode('/', $this->value);
			array_shift($folder);
			array_pop($folder);
			$folder = implode('/', $folder);
		}
		/*elseif (file_exists(storage_path() . '/' . app('component')->params('com_media')->get('image_path', 'images') . '/' . $directory))
		{
			$folder = $directory;
		}*/
		else
		{
			$folder = '';
		}

		// The button.
		$html[] = '	<span class="input-cell">';
		$html[] = '		<a class="button modal" title="' . trans('JLIB_FORM_BUTTON_SELECT') . '"' . ' href="'
			. ($this->element['readonly'] ? ''
			: ($link ? $link
				: '/media?tmpl=component&amp;asset=' . implode('/',$asset) . '&amp;author='
				. $this->form->getValue($authorField)) . '&amp;fieldid=' . $this->id . '&amp;folder=' . $folder) . '"'
			. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
		$html[] = trans('JLIB_FORM_BUTTON_SELECT') . '</a>';
		$html[] = '	</span>';

		$html[] = '	<span class="input-cell">';
		$html[] = '		<a class="button" title="' . trans('JLIB_FORM_BUTTON_CLEAR') . '"' . ' href="#" onclick="';
		$html[] = 'jInsertFieldValue(\'\', \'' . $this->id . '\');';
		$html[] = 'return false;';
		$html[] = '">';
		$html[] = trans('JLIB_FORM_BUTTON_CLEAR') . '</a>';
		$html[] = '	</span>';

		$html[] = '</div>';

		// The Preview.
		$preview = (string) $this->element['preview'];
		$showPreview = true;
		$showAsTooltip = false;
		switch ($preview)
		{
			case 'false':
			case 'none':
				$showPreview = false;
				break;
			case 'true':
			case 'show':
				break;
			case 'tooltip':
			default:
				$showAsTooltip = true;
				$options = array(
					'onShow' => 'jMediaRefreshPreviewTip',
				);
				//Behavior::tooltip('.hasTipPreview', $options);
				break;
		}

		if ($showPreview)
		{
			if ($this->value && file_exists(storage_path() . '/' . $this->value))
			{
				$src = url('/') . $this->value;
			}
			else
			{
				$src = '';
			}

			$attr = array(
				'id'    => $this->id . '_preview',
				'class' => 'media-preview',
				'style' => 'max-width:160px; max-height:100px;'
			);
			$img = ''; //Asset::image($src, $lang->txt('JLIB_FORM_MEDIA_PREVIEW_ALT'), $attr);
			$previewImg = '<div id="' . $this->id . '_preview_img"' . ($src ? '' : ' style="display:none"') . '>' . $img . '</div>';
			$previewImgEmpty = '<div id="' . $this->id . '_preview_empty"' . ($src ? ' style="display:none"' : '') . '>' . trans('JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';

			//$html[] = '<div class="media-preview fltlft">';
			if ($showAsTooltip)
			{
				$tooltip = $previewImgEmpty . $previewImg;
				$options = array(
					'title' => htmlspecialchars(trans('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'), ENT_COMPAT, 'UTF-8'),
					'text'  => htmlspecialchars(trans('JLIB_FORM_MEDIA_PREVIEW_TIP_TITLE'), ENT_COMPAT, 'UTF-8'),
					'class' => 'hasTipPreview'
				);
				$html[] = '<span class="' . $options['class'] . '" title="' . $options['title'] . '::' . htmlspecialchars($tooltip, ENT_COMPAT, 'UTF-8') . '">' . $options['text'] . '</span>';

				//Behavior::tooltip('.hasTipPreview', $options);
			}
			else
			{
				$html[] = ' ' . $previewImgEmpty;
				$html[] = ' ' . $previewImg;
			}
			//$html[] = '</div>';
		}

		return implode("\n", $html);
	}
}
