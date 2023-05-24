<?php

namespace App\Halcyon\Form\Fields;

//use App\Halcyon\Form\Field;
//use App\Halcyon\Html\Editor as Wysiwyg;
use App\Modules\Core\Events\EditorIsRendering;

/**
 * An editarea field for content creation
 */
class Editor extends Textarea
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Editor';

	/**
	 * The Editor object.
	 *
	 * @var  object
	 */
	protected $editor;

	/**
	 * Method to get the field input markup for the editor area
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$rows = (int) $this->element['rows'];
		$cols = (int) $this->element['cols'];
		$height = ((string) $this->element['height']) ? (string) $this->element['height'] : '250';
		$width = ((string) $this->element['width']) ? (string) $this->element['width'] : '100%';
		$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];

		// Build the buttons array.
		$buttons = (string) $this->element['buttons'];

		if ($buttons == 'true' || $buttons == 'yes' || $buttons == '1')
		{
			$buttons = true;
		}
		elseif ($buttons == 'false' || $buttons == 'no' || $buttons == '0')
		{
			$buttons = false;
		}
		else
		{
			$buttons = explode(',', $buttons);
		}

		$hide = ((string) $this->element['hide']) ? explode(',', (string) $this->element['hide']) : array();

		// Get an editor object.
		$editor = $this->getEditor();

		if (!$editor)
		{
			return parent::getInput();
		}

		event($event = new EditorIsRendering($this->name, $this->form->getValue($authorField), [
			'width' => $width,
			'height' => $height,
			'cols' => $cols,
			'rows' => $rows
		]));

		return $event->render();
	}

	/**
	 * Method to get a Editor object based on the form field.
	 *
	 * @return  object  The Editor object.
	 */
	protected function getEditor()
	{
		// Only create the editor if it is not already created.
		if (empty($this->editor))
		{
			// Initialize variables.
			$editor = null;

			// Get the editor type attribute. Can be in the form of: editor="desired|alternative".
			$type = trim((string) $this->element['editor']);

			if ($type)
			{
				// Get the list of editor types.
				$types = explode('|', $type);

				// Get the database object.
				$db = app('db');

				// Iterate over teh types looking for an existing editor.
				foreach ($types as $element)
				{
					// Build the query.
					$editor = $db->table('extensions')
						->select('element')
						->where('element', '=', $element)
						->where('folder', '=', 'editors')
						->where('enabled', '=', '1')
						->limit(1)
						->first();

					// If an editor was found stop looking.
					if ($editor)
					{
						break;
					}
				}
			}

			// Create the Editor instance based on the given editor.
			$this->editor = $editor; //Wysiwyg::getInstance($editor);
		}

		return $this->editor;
	}

	/**
	 * Method to get the Editor output for an onSave event.
	 *
	 * @return  string  The Editor object output.
	 */
	public function save()
	{
		return $this->getEditor()->save($this->id);
	}
}
