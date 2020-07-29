<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

return [
	'widget name' => 'Menu',
	'widget desc' => 'This widgets displays a menu on the frontend.',
	'params' => [
		'allchildren desc' => 'Expand the menu and make its sub-menu items always visible.',
		'allchildren' => 'Show Sub-menu Items',
		'class desc' => 'A suffix to be applied to the CSS class of the menu items',
		'class' => 'Menu Class Suffix',
		'endlevel desc' => 'Level to stop rendering the menu at. If you choose "All", all levels will be shown depending on "Show Sub-menu Items" setting.',
		'endlevel' => 'End Level',
		'menutype desc' => 'Select a menu in the list',
		'menutype' => 'Select Menu',
		'startlevel desc' => 'Level to start rendering the menu at. Setting the start and end levels to the same # and setting "Show Sub-menu Items" to yes will only display that single level.',
		'startlevel' => 'Start Level',
		'tag id desc' => 'An ID attribute to assign to the root UL tag of the menu (optional)',
		'tag id' => 'Menu Tag ID',
		'target desc' => 'JavaScript values to position a popup window, e.g. top=50,left=50,width=200,height=300',
		'target' => 'Target Position',
	],
];
