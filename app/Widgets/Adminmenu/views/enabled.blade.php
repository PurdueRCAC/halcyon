<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

use App\Widgets\Adminmenu\Node;

$shownew = (boolean) $params->get('shownew', 1);
$user = auth()->user();
$active = app('request')->segment(2);

$menu->addChild(
	new Node(trans('widget.adminmenu::adminmenu.dashboard'), route('admin.dashboard.index'), 'class:dashboard', ($active == 'dashboard')), true
);

$menu->getParent();

//
// Site SubMenu
//
$menu->addChild(
	new Node(trans('widget.adminmenu::adminmenu.configuration'), route('admin.config'), 'class:settings', in_array($active, ['info', 'core', 'config', 'checkin', 'cache', 'redirect', 'history'])), true
);

if ($user->can('admin'))
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.configuration'), route('admin.config'), 'class:config', ($active == 'config')));
	//$menu->addSeparator();
}

$chm = $user->can('admin messages');
$cam = $user->can('manage cache');
$cst = $user->can('manage cron');

if ($chm || $cam || $cst)
{
	if ($chm)
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.messages'), route('admin.messages.index'), 'class:maintenance', ($active == 'messages')));
	}

	if ($cst)
	{
		//$menu->getParent();
		//$menu->addSeparator();
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.scheduled tasks'), route('admin.cron.index'), 'class:scheduled', ($active == 'cron')));
	}

	/*if ($user->can('manage checkin'))
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.GLOBAL_CHECKIN'), 'index.php?option=checkin', 'class:checkin', ($active == 'checkin')));
		$menu->addSeparator();
	}
	if ($user->can('manage cache'))
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.CLEAR_CACHE'), 'index.php?option=cache', 'class:clear', ($active == 'cache')));
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.PURGE_EXPIRED_CACHE'), 'index.php?option=cache&view=purge', 'class:purge', ($active == 'cache')));
		$menu->addSeparator();
	}*/

	//$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.SYS_LDAP'), 'index.php?option=system&controller=ldap', 'class:ldap', ($active == 'system')));
	//$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.SYS_GEO'), 'index.php?option=system&controller=geodb', 'class:geo', ($active == 'system')));
	//$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.SYS_APC'), 'index.php?option=system&controller=apc', 'class:apc', ($active == 'system')));
	//$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.SYS_ROUTES'), 'index.php?option=redirect', 'class:routes', ($active == 'redirect')));

	//$menu->getParent();
}

//$menu->addSeparator();
if ($user->can('admin'))
{
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.activity log'), route('admin.history.index'), 'class:history', ($active == 'history'))
	);
	$menu->addSeparator();
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.system info'), route('admin.core.sysinfo'), 'class:info', ($active == 'core'))
	);
}
/*$menu->addChild(
	new Node(trans('widget.adminmenu::adminmenu.LOGOUT'), route('logout'), 'class:logout')
);*/

$menu->getParent();

//
// Users Submenu
//
if ($user->can('manage users') || $user->can('manage contactreports'))
{
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.users'), route('admin.users.index'), 'class:users', in_array($active, ['users', 'groups', 'contactreports'])), true
	);
	$createUser = $shownew && $user->can('create users');
	$createGrp  = $user->can('create users.roles');

	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.users'), route('admin.users.index'), 'class:members', $active == 'users'), $createUser
	);
	/*if ($createUser)
	{
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.add user'), route('admin.users.create'), 'class:newuser')
		);
	}*/
	$menu->getParent();

	if ($user->can('manage groups'))
	{
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.groups'), route('admin.groups.index'), 'class:groups', ($active == 'groups'))
		);
	}

	if ($createGrp)
	{
		$menu->addSeparator();
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.roles'), route('admin.users.roles'), 'class:roles', $active == 'users'), $createUser
		);
		/*if ($user->can('create users.roles'))
		{
			$menu->addChild(
				new Node(trans('widget.adminmenu::adminmenu.add role'), route('admin.users.roles.create'), 'class:newrole')
			);
			$menu->getParent();
		}*/
		$menu->getParent();

		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.levels'), route('admin.users.levels'), 'class:levels', $active == 'users'), $createUser
		);

		/*if ($user->can('create users.accesslevels'))
		{
			$menu->addChild(
				new Node(trans('widget.adminmenu::adminmenu.add level'), route('admin.users.levels.create'), 'class:newlevel')
			);
			$menu->getParent();
		}*/
		$menu->getParent();
	}

	$menu->addSeparator();
	/*$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.notes'), route('admin.users.notes'), 'class:user-note'), $createUser
	);

	if ($createUser)
	{
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.add note'), route('admin.users.notes.create'), 'class:newarticle')
		);
		$menu->getParent();
	}*/

	if ($user->can('manage contactreports'))
	{
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.contact reports'), route('admin.contactreports.index'), 'class:contactreport', ($active == 'contactreports'))
		);
	}

	/*if ($createUser)
	{
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.COM_CONTENT_NEW_CATEGORY'), 'index.php?option=categories&task=category.add&extension=members', 'class:newarticle')
		);
		$menu->getParent();
	}

	$menu->addSeparator();
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.MASS_MAIL_USERS'), 'index.php?option=members&controller=mail', 'class:massmail')
	);*/

	$menu->getParent();
}

//
// Menus Submenu
//
if ($user->can('manage menus'))
{
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.menus'), route('admin.menus.index'), 'class:menus', ($active == 'menus')), true
	);
	$createMenu = $shownew; // && User::authorise('core.create', 'menus');

	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.menu manager'), route('admin.menus.index'), 'class:menumgr'), $createMenu
	);
	/*if ($createMenu)
	{
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.MENU_MANAGER_NEW_MENU'), 'index.php?option=menus&view=menu&layout=edit', 'class:newmenu')
		);
		
	}*/
	$menu->getParent();
	$menu->addSeparator();

	// Menu Types
	foreach ($menus as $menuType)
	{
		$alt = '*' . $menuType->sef . '*';
		if ($menuType->home == 0)
		{
			$titleicon = '';
		}
		elseif ($menuType->home == 1 && $menuType->language == '*')
		{
			$titleicon = ' <span class="home" title="' . trans('widget.adminmenu::adminmenu.HOME_DEFAULT') . '">' . '*' . '</span>';
		}
		elseif ($menuType->home > 1)
		{
			$titleicon = ' <span class="home multiple" title="' . trans('widget.adminmenu::adminmenu.HOME_MULTIPLE') . '">' . $menuType->home . '</span>';
		}
		else
		{
			$titleicon = ' <span title="' . $menuType->title_native . '">' . $alt . '</span>';
		}

		$menu->addChild(
			new Node($menuType->title, route('admin.menus.items', ['menutype' => $menuType->menutype]), 'class:menu', null, null, $titleicon), $createMenu
		);

		/*if ($createMenu)
		{
			$menu->addChild(
				new Node(trans('widget.adminmenu::adminmenu.MENU_MANAGER_NEW_MENU_ITEM'), 'index.php?option=menus&view=item&layout=edit&menutype=' . $menuType->menutype, 'class:newarticle')
			);
		}*/
		$menu->getParent();
	}
	$menu->getParent();
}

//
// Content Submenu
//
if ($user->can('manage pages') || $user->can('manage media') || $user->can('manage knowledge') || $user->can('manage news'))
{
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.content'), route('admin.pages.index'), 'class:file-text', in_array($active, ['pages', 'knowledge', 'media', 'news'])), true
	);
	if ($user->can('manage pages'))
	{
		$createContent = $shownew && $user->can('create pages');
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.page manager'), route('admin.pages.index'), 'class:article', ($active == 'pages')), $createContent
		);
		$menu->getParent();
	}
	/*if ($createContent)
	{
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.new page'), route('admin.pages.create'), 'class:newarticle')
		);
		$menu->getParent();
	}*/

	/*$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.CATEGORY_MANAGER'), route('admin.categories.index', ['extension' => 'pages']), 'class:category'), $createContent
	);
	if ($createContent)
	{
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.COM_CONTENT_NEW_CATEGORY'), route('admin.categories.create', ['extension' => 'pages']), 'class:newarticle')
		);
		$menu->getParent();
	}
	*/
	if ($user->can('manage news'))
	{
		//$menu->addSeparator();
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.news'), route('admin.news.index'), 'class:news', ($active == 'news')));
	}

	if ($user->can('manage knowledge'))
	{
		//$menu->addSeparator();
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.knowledge'), route('admin.knowledge.index'), 'class:knowledge', ($active == 'knowledge')));
	}

	if ($user->can('manage media'))
	{
		$menu->addSeparator();
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.media manager'), route('admin.media.index'), 'class:media', ($active == 'media')));
	}

	$menu->getParent();
}

if ($user->can('manage resources') || $user->can('manage queues') || $user->can('manage storage'))
{
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.resources'), route('admin.resources.index'), 'class:server', in_array($active, ['resources', 'queues', 'storage'])), true
	);

	if ($user->can('manage queues'))
	{
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.queue manager'), route('admin.queues.index'), 'class:queues', ($active == 'queues'))
		);
	}

	if ($user->can('manage queues'))
	{
		//$menu->addSeparator();
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.resources'), route('admin.resources.index'), 'class:resources', $active == 'resources')
		);
	}

	if ($user->can('manage storage'))
	{
		//$menu->addSeparator();
		$menu->addChild(
			new Node(trans('widget.adminmenu::adminmenu.storage manager'), route('admin.storage.index'), 'class:storage', $active == 'storage')
		);
	}

	$menu->getParent();
}

if ($user->can('manage orders'))
{
	$pending = App\Modules\Orders\Models\Order::whereIn('notice', [1, 2])->count();
	if ($pending)
	{
		$pending = ' <span class="badge badge-danger">' . $pending . '</span>';
	}
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.order manager') . $pending, route('admin.orders.index'), 'class:shopping-cart', $active == 'orders'), true
	);

	$menu->getParent();
}

//
// Components Submenu
//

// Check if there are any modules, otherwise, don't render the menu
$mm = $user->can('manage widgets');
$pm = $user->can('manage listeners');

if ($mm || $pm || count($modules))
{
	$skip = array(
		'core', 'cron', 'users', 'groups',
		'system', 'history', 'messages', 'orders',
		'resources', 'queues', 'storage',
		'contactreports', 'knowledge', 'news'
	);
	$actives = array('widgets', 'listeners');

	if (count($modules))
	{
		foreach ($modules as $module)
		{
			if (in_array($module->element, $skip))
			{
				continue;
			}

			$actives[] = $module->element;
		}
	}

	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.extensions'), '#', 'class:extensions', in_array($active, $actives)), true);

	if ($mm)
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.widget manager'), route('admin.widgets.index'), 'class:widgets', ($active == 'widgets')));
	}

	if ($pm)
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.listener manager'), route('admin.listeners.index'), 'class:listeners', ($active == 'listeners')));
	}

	if ($modules)
	{
		$menu->addSeparator();

		foreach ($modules as $module)
		{
			if (in_array($module->element, $skip))
			{
				continue;
			}

			$actives[] = $module->element;
		}

		foreach ($modules as $component)
		{
			if (in_array($component->element, $skip))
			{
				continue;
			}

			if (!empty($component->submenu))
			{
				// This component has a db driven submenu.
				$menu->addChild(new Node($component->text, $component->link, $component->class), true);
				foreach ($component->submenu as $sub)
				{
					$menu->addChild(new Node($sub->text, $sub->link, $sub->class));
				}
				$menu->getParent();
			}
			else
			{
				$menu->addChild(new Node($component->text, $component->link, $component->class, ($active == $component->element)));
			}
		}
		$menu->getParent();
	}
}
//
// Extensions Submenu
//
//$im = $user->can('manage installer');
//$mm = $user->can('manage widgets');
//$pm = $user->can('manage listeners');
//$tm = $user->can('manage themes');
//$lm = $user->can('manage languages');

if ($user->can('manage themes'))
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.theme manager'), route('admin.themes.index'), 'class:modules', ($active == 'themes')), true);
	$menu->getParent();
}
/*
if ($im || $mm || $pm || $tm || $lm)
{
	$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.extensions'), 'admin.core.extensions', 'class:extensions', in_array($active, ['widgets', 'listeners', 'themes', 'languages'])), true);

	if ($im)
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.extensions manager'), 'admin.core.extensions', 'class:install', ($active == 'installer')));
		$menu->addSeparator();
	}

	if ($mm)
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.widget manager'), route('admin.widgets.index'), 'class:widgets', ($active == 'widgets')));
	}

	if ($pm)
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.listener manager'), route('admin.listeners.index'), 'class:listeners', ($active == 'listeners')));
	}

	if ($tm)
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.theme manager'), route('admin.themes.index'), 'class:themes', ($active == 'themes')));
	}

	if ($lm)
	{
		$menu->addChild(new Node(trans('widget.adminmenu::adminmenu.language manager'), 'admin.core.languages', 'class:language', ($active == 'languages')));
	}
	$menu->getParent();
}

//
// Help Submenu
//
if ($params->get('showhelp', 0) == 1)
{
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.help'), '#', 'class:help-circle', ($active == 'help')), true
	);
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.help pages'), 'index.php?option=help', 'class:help')
	);
	$menu->addSeparator();
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.documentation'), 'http://hubzero.org/documentation', 'class:help', false, '_blank')
	);
	$menu->addChild(
		new Node(trans('widget.adminmenu::adminmenu.help'), 'http://www.rcac.purdue.edu/help', 'class:help-docs', false, '_blank')
	);
	$menu->getParent();
}*/

$menu->renderMenu('adminmenu');
