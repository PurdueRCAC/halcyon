<?php
use App\Widgets\Adminmenu\Node;

$user = auth()->user();

//
// Dashboard SubMenu
//
$menu->addChild(new Node(
	trans('widget.adminmenu::adminmenu.dashboard'),
	null,
	'class:' . (isset($groupings['dashboard']) ? $groupings['dashboard'] : 'dashboard') . ' disabled'
));

//
// Site SubMenu
//
$menu->addChild(new Node(
	trans('widget.adminmenu::adminmenu.configuration'),
	null,
	'class:' . (isset($groupings['system']) ? $groupings['system'] : 'settings') . ' disabled'
));

//
// Users Submenu
//
if ($user->can('manage users')
 || $user->can('manage groups')
 || !empty($modules['users']))
{
	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.users'),
		null,
		'class:' . (isset($groupings['users']) ? $groupings['users'] : 'users') . ' disabled'
	));
}

//
// Menus Submenu
//
if ($user->can('manage menus'))
{
	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.menus'),
		null,
		'class:' . (isset($groupings['menus']) ? $groupings['menus'] : 'menus') . ' disabled'
	));
}

//
// Content Submenu
//
if ($user->can('manage pages')
 || $user->can('manage media')
 || !empty($modules['content']))
{
	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.content'),
		null,
		'class:' . (isset($groupings['content']) ? $groupings['content'] : 'file-text') . ' disabled'
	));
}

//
// Custom groupings
//
foreach ($modules as $group => $mods)
{
	if (in_array($group, ['dashboard', 'system', 'users', 'menus', 'themes', 'content', 'extensions']))
	{
		continue;
	}

	foreach ($mods as $i => $mod)
	{
		if ($i == 0)
		{
			$menu->addChild(
				new Node(
					$mod->text,
					null,
					'class:' . (isset($groupings[$group]) ? $groupings[$group] : $mod->element) . ' disabled'
				)
			);
		}
	}
}

//
// Extensions Submenu
//
$mm = $user->can('manage widgets');
$pm = $user->can('manage listeners');
$lm = $user->can('manage languages');

if ($mm || $pm || $lm)
{
	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.extensions'),
		null,
		'class:' . (isset($groupings['extensions']) ? $groupings['extensions'] : 'extensions') . ' disabled'
	));
}

//
// Themes Submenu
//
if ($user->can('manage themes'))
{
	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.theme manager'),
		null,
		'class:' . (isset($groupings['themes']) ? $groupings['themes'] : 'modules') . ' disabled'
	));
}

$menu->renderMenu('adminmenu', 'disabled');
