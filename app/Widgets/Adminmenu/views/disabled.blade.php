<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use App\Widgets\Adminmenu\Node;

$user = auth()->user();

$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.dashboard'), null, 'class:dashboard disabled'));

//
// Site SubMenu
//
$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.configuration'), null, 'class:settings disabled'));

//
// Users Submenu
//
if ($user->can('manage users') || $user->can('manage groups'))
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.users'), null, 'class:users disabled'));
}

//
// Menus Submenu
//
if ($user->can('manage menus'))
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.menus'), null, 'class:menus disabled'));
}

//
// Content Submenu
//
if ($user->can('manage pages') || $user->can('manage news') || $user->can('manage knowledge') || $user->can('manage media'))
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.content'), null, 'class:file-text disabled'));
}

//
// Resources Submenu
//
if ($user->can('manage resources') || $user->can('manage queues') || $user->can('manage storage'))
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.resources'), null, 'class:server disabled'));
}

if ($user->can('manage orders'))
{
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.order manager'), null, 'class:shopping-cart')
	);
}

//
// Extensions Submenu
//
$mm = $user->can('manage widgets');
$pm = $user->can('manage listeners');
$lm = $user->can('manage languages');

if ($mm || $pm || $lm)
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.extensions'), null, 'class:extensions disabled'));
}

if ($user->can('manage themes'))
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.theme manager'), null, 'class:modules disabled'));
}

//
// Help Submenu
//
/*if ($params->get('showhelp', 0) == 1)
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.HELP'), null, 'class:help disabled'));
}*/

$menu->renderMenu('menu', 'disabled');
