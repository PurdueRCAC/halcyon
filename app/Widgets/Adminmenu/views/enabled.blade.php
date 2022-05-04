<?php
use App\Widgets\Adminmenu\Node;

$shownew = (boolean) $params->get('shownew', 1);
$user = auth()->user();
$active = app('request')->segment(2);

$menu->addChild(new Node(
	trans('widget.adminmenu::adminmenu.dashboard'),
	route('admin.dashboard.index'),
	'class:' . (isset($groupings['dashboard']) ? $groupings['dashboard'] : 'dashboard'),
	($active == 'dashboard')
), true);

$menu->getParent();

//
// Site SubMenu
//
$chm = $user->can('manage messages');
$cam = $user->can('manage cache');
$cst = $user->can('manage cron');

$menu->addChild(new Node(
	trans('widget.adminmenu::adminmenu.system'),
	route('admin.core.sysinfo'),
	'class:' . (isset($groupings['system']) ? $groupings['system'] : 'settings'),
	in_array($active, ['info', 'core', 'config', 'checkin', 'cache', 'redirect', 'history'])
), true);

if ($chm || $cam || $cst)
{
	if ($chm && Module::isEnabled('messages'))
	{
		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.messages'),
			route('admin.messages.index'),
			'class:maintenance',
			($active == 'messages')
		));
	}

	if ($cst && Module::isEnabled('cron'))
	{
		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.scheduled tasks'),
			route('admin.cron.index'),
			'class:scheduled',
			($active == 'cron')
		));
	}
}

if ($user->can('admin'))
{
	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.activity log'),
		route('admin.history.index'),
		'class:history',
		($active == 'history')
	));

	$menu->addSeparator();

	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.system info'),
		route('admin.core.sysinfo'),
		'class:sysinfo',
		($active == 'core')
	));
}

$menu->getParent();

//
// Users Submenu
//
if ($user->can('manage users')
 || $user->can('manage groups')
 || !empty($modules['users']))
{
	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.users'),
		route('admin.users.index'),
		'class:' . (isset($groupings['users']) ? $groupings['users'] : 'users'),
		in_array($active, ['users', 'groups', 'contactreports'])
	), true);

	$createUser = $shownew && $user->can('create users');

	if ($user->can('manage users'))
	{
		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.users'),
			route('admin.users.index'),
			'class:members',
			$active == 'users'
		), $createUser);

		$menu->getParent();
	}

	if ($user->can('manage groups') && Module::isEnabled('groups'))
	{
		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.groups'),
			route('admin.groups.index'),
			'class:groups',
			($active == 'groups')
		));
	}

	if ($user->can('create users.roles'))
	{
		$menu->addSeparator();

		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.roles'),
			route('admin.users.roles'),
			'class:roles',
			$active == 'users'
		), $createUser);

		$menu->getParent();

		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.levels'),
			route('admin.users.levels'),
			'class:levels',
			$active == 'users'
		), $createUser);

		$menu->getParent();
	}

	$addedSeparator = false;

	foreach ($modules['users'] as $mod)
	{
		if (in_array($mod->element, ['users', 'groups']))
		{
			continue;
		}

		if (!$addedSeparator)
		{
			$menu->addSeparator();
			$addedSeparator = true;
		}

		$menu->addChild(new Node(
			$mod->text,
			$mod->link,
			'class:' . $mod->element,
			($active == $mod->element)
		));
	}

	$menu->getParent();
}

//
// Menus Submenu
//
if ($user->can('manage menus') && Module::isEnabled('menus'))
{
	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.menus'),
		route('admin.menus.index'),
		'class:' . (isset($groupings['menus']) ? $groupings['system'] : 'menus'),
		($active == 'menus')
	), true);

	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.menu manager'),
		route('admin.menus.index'),
		'class:menumgr'
	), $shownew);

	$menu->getParent();
	$menu->addSeparator();

	// Menu Types
	foreach ($menus as $menuType)
	{
		$menu->addChild(new Node(
			$menuType->title,
			route('admin.menus.items', ['menutype' => $menuType->menutype]),
			'class:menu'//,
			//null,
			//null,
			//$titleicon
		), true);

		$menu->getParent();
	}
	$menu->getParent();
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
		route('admin.pages.index'),
		'class:' . (isset($groupings['content']) ? $groupings['content'] : 'file-text'),
		in_array($active, ['pages', 'knowledge', 'media', 'news'])
	), true);

	if ($user->can('manage pages') && Module::isEnabled('pages'))
	{
		$createContent = $shownew && $user->can('create pages');

		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.page manager'),
			route('admin.pages.index'),
			'class:article',
			($active == 'pages')
		), $createContent);

		$menu->getParent();
	}

	foreach ($modules['content'] as $mod)
	{
		if (in_array($mod->element, ['pages', 'media']))
		{
			continue;
		}

		$menu->addChild(new Node(
			$mod->text,
			$mod->link,
			'class:' . $mod->element,
			($active == $mod->element)
		));
	}

	if ($user->can('manage media') && Module::isEnabled('media'))
	{
		$menu->addSeparator();
		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.media manager'),
			route('admin.media.index'),
			'class:media',
			($active == 'media')
		));
	}

	$menu->getParent();
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
					$mod->link,
					'class:' . (isset($groupings[$group]) ? $groupings[$group] : $mod->element),
					($active == $mod->element || ($i == 0 && isset($mods[$mod->element])))
				),
				true
			);
		}

		if (count($mods) > 1)
		{
			$menu->addChild(
				new Node(
					$mod->text,
					$mod->link,
					$mod->element,
					($active == $mod->element || ($i == 0 && isset($mods[$mod->element])))
				)
			);
		}
	}

	$menu->getParent();
}

//
// Extensions Submenu
//
$mm = $user->can('manage modules');
$mw = $user->can('manage widgets');
$pm = $user->can('manage listeners');

if ($mm || $mw || $pm || count($modules['extensions']))
{
	$skip = array(
		'core', 'cron', 'users', 'system', 'history',
		'groups', 'messages', 'orders',
		'resources', 'queues', 'storage',
		'contactreports', 'knowledge', 'news'
	);
	$actives = array('modules', 'widgets', 'listeners');

	if (count($modules['extensions']))
	{
		foreach ($modules['extensions'] as $module)
		{
			if (in_array($module->element, $skip))
			{
				continue;
			}

			$actives[] = $module->element;
		}
	}

	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.extensions'),
		route('admin.modules.index'),
		'class:' . (isset($groupings['extensions']) ? $groupings['extensions'] : 'extensions'),
		in_array($active, $actives)
	), true);

	if ($mm)
	{
		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.module manager'),
			route('admin.modules.index'),
			'class:module',
			($active == 'modules')
		));
	}

	if ($mw)
	{
		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.widget manager'),
			route('admin.widgets.index'),
			'class:widgets',
			($active == 'widgets')
		));
	}

	if ($pm)
	{
		$menu->addChild(new Node(
			trans('widget.adminmenu::adminmenu.listener manager'),
			route('admin.listeners.index'),
			'class:listeners',
			($active == 'listeners')
		));
	}

	if ($modules)
	{
		$menu->addSeparator();

		foreach ($modules['extensions'] as $mod)
		{
			if (in_array($mod->element, ['core', 'listeners', 'widgets']))
			{
				continue;
			}

			$menu->addChild(new Node(
				$mod->text,
				$mod->link,
				$mod->class,
				($active == $mod->element)
			));
		}
		$menu->getParent();
	}
}

//
// Themes
//
if ($user->can('manage themes') && Module::isEnabled('themes'))
{
	$menu->addChild(new Node(
		trans('widget.adminmenu::adminmenu.theme manager'),
		route('admin.themes.index'),
		'class:' . (isset($groupings['themes']) ? $groupings['themes'] : 'modules'),
		($active == 'themes')
	), true);

	if (count($modules['themes']) > 1)
	{
		$menu->addSeparator();

		foreach ($modules['themes'] as $mod)
		{
			if (in_array($mod->element, ['themes']))
			{
				continue;
			}

			$menu->addChild(new Node(
				$mod->text,
				$mod->link,
				'class:' . $mod->element,
				($active == $mod->element)
			));
		}
	}

	$menu->getParent();
}

$menu->renderMenu('adminmenu');
