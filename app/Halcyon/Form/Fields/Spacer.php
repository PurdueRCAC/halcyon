<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Form\Field;

/**
 * Provides spacer markup to be used in form layouts.
 */
class Spacer extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Spacer';

	/**
	 * Method to get the field input markup for a spacer.
	 * The spacer does not have accept input.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		return ' ';
	}

	/**
	 * Method to get the field label markup for a spacer.
	 * Use the label text or name from the XML element as the spacer or
	 * Use a hr="true" to automatically generate plain hr markup
	 *
	 * @return  string  The field label markup.
	 */
	protected function getLabel()
	{
		$html = array();
		$class = $this->element['class'] ? (string) $this->element['class'] : '';

		$html[] = '<span class="spacer">';
		$html[] = '<span class="before"></span>';
		$html[] = '<span class="' . $class . '">';
		if ((string) $this->element['hr'] == 'true')
		{
			$html[] = '<hr class="' . $class . '" />';
		}
		else
		{
			$label = '';

			// Get the label text from the XML element, defaulting to the element name.
			$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
			$text = $this->translateLabel ? trans($text) : $text;

			// Build the class for the label.
			$class = !empty($this->description) ? 'hasTip' : '';
			$class = $this->required == true ? $class . ' required' : $class;

			// Add the opening label tag and main attributes attributes.
			$label .= '<label id="' . $this->id . '-lbl" class="' . $class . '"';

			// If a description is specified, use it to build a tooltip.
			if (!empty($this->description))
			{
				$label .= ' title="'
					. htmlspecialchars(
					trim($text, ':') . '::' . ($this->translateDescription ? trans($this->description) : $this->description),
					ENT_COMPAT, 'UTF-8'
				) . '"';
			}

			// Add the label text and closing tag.
			$label .= '>' . $text . '</label>';
			$html[] = $label;
		}
		$html[] = '</span>';
		$html[] = '<span class="after"></span>';
		$html[] = '</span>';

		return implode('', $html);
	}

	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 */
	protected function getTitle()
	{
		return $this->getLabel();
	}
}
