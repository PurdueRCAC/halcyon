
	<h1>Menu Manager: Menus</h1>

	<ol class="toc">
		<li><a href="#description">Description</a></li>
		<li><a href="#column-headers">Column Headers</a></li>
		<li><a href="#filters">List Filters</a></li>
		<li><a href="#toolbar">Toolbar</a></li>
		@if (auth()->user()->can('admin menus'))
		<li><a href="#options">Options</a>
			<ol>
				<li><a href="#permissions">Permissions</a>
			</ol>
		</li>
		@endif
	</ol>

	<h2 id="description">Description</h2>

	<p>Menus allow a user to navigate through the site. A menu is an object which contains one or more menu items. Each menu item points to a logical page on the site. A menu widget is required to place the menu on the page. One menu can have more than one widget. For example, one widget might show only the first level menu items and a second widget might show the level 2 menu items.</p>
	<p>The process for adding a menu to the site is normally as follows:</p>
	<ol>
		<li>Create a new menu (using this screen).</li>
		<li>Create one or more new menu items on the menu. Each menu item will have a specific menu item type.</li>
		<li>Create one or more menu widgets to display the menu on the site. When you create the widget, you will select which menu items (pages) the widget will show on.</li>
	</ol>

	<h2 id="column-headers">Column Headers</h2>
	<p>In the table containing the users from your site, you will see different columns. Here you can read what they mean and what is displayed in that column.</p>
	<dl>
		<dt>Checkbox</dt><dd>Check this box to select one or more items. To select all items, check the box in the column heading. After one or more boxes are checked, click a toolbar button to take an action on the selected item or items. Many toolbar actions, such as Publish and Unpublish, can work with multiple items. <strong>Note: Some toolbar buttons will not appear until one or more rows are selected.</strong></dd>
		<dt>ID</dt><dd>This is a unique identification number for this item assigned automatically by the CMS. It is used to identify the item internally, and you cannot change this number. When creating a new item, this field displays 0 until you save the new entry, at which point a new ID is assigned to it.</dd>
		<dt>Title</dt><dd>The name of the menu.</dd>
		<dt># Published</dt><dd>Number of published menu items in this menu.</dd>
		<dt># Unpublished</dt><dd>Number of unpublished menu items in this menu.</dd>
		<dt># Trashed</dt><dd>Number of trashed menu items in this menu.</dd>
		<dt>Widgets linked to the menu</dt><dd>Lists any menu widgets associated with the menu. The column shows the widget's name and position.</dd>
	</dl>

	<h2 id="filters">List Filters</h2>

	<p><strong>Search</strong> - In the upper left is a Search field where entries can be found by title, description, or ID.</p>

	<p>In the upper right, above the column headings, are three drop-down list boxes as shown below.</p>

	<dl>
		<dt>State</dt><dd>Select the desired state to limit the list based on state.</dd>
	</dl>

	<p>Below the list of records are the pagination controls. When the number of items is more than one page, you will see a page control bar as shown below.</p>

	<dl>
		<dt>Display #</dt><dd>Select the number of items to show on one page.</dd>
		<dt>Prev</dt><dd>Click to go to the previous page.</dd>
		<dt>Page numbers</dt><dd>Click to go to the desired page.</dd>
		<dt>Next</dt><dd>Click to go to the next page.</dd>
	</dl>

	<h2 id="toolbar">Toolbar</h2>

	<p>At the top right you will see the toolbar:</p>

	<dl>
		<dt>New</dt><dd>Opens the editing screen to create a new user.</dd>
		<dt>Delete</dt><dd>Deletes the selected users. <strong>Requires selecting one or more records</strong>.</dd>
		@if (auth()->user()->can('admin menus'))
		<dt>Options</dt><dd>Opens the Options window where settings such as default parameters or permissions can be edited.</dd>
		@endif
		<dt>Help</dt><dd>Opens this help screen.</dd>
	</dl>

@if (auth()->user()->can('admin menus'))
	<h2 id="options">Options</h2>
	<p>Click the Options button to open the <strong>Menu Manager Options</strong> window which lets you configure this module.</p>

	<h3 id="permissions">Permissions</h3>

	<p>This screen allows you to set the module permissions. This is important to consider if you have sites with many different user categories all of whom need to have different accessibilities to the module. The screenshot below describes what you should see and the text below that describes what each permission level gives the user access to:</p>

	<p>You work on one Role at a time by opening the slider for that role. You change the permissions in the Select New Settings drop-down list boxes.
	The options for each value are <code>Inherited</code>, <code>Allowed</code>, or <code>Denied</code>. The Calculated Setting column shows you the setting in effect. It is either <span class="badge badge-warning">Not Allowed (the default)</span>, <span class="badge badge-success">Allowed</span>, or  <span class="badge badge-danger">Denied</span>.
	Note that the Calculated Setting column is not updated until you press the Save button in the toolbar. To check that the settings are what you want, press the Save button and check the Calculated Settings column.</p>

	<p>Default values for roles are set in the User Manager options.</p>

	<dl>
		<dt>Access Administration Interface</dt>
		<dd>Open the Menu Manger screens</dd>
		<dt>Configure</dt>
		<dd>Open the Menu Manager options screen (the modal window these options are in)</dd>
		<dt>Create&#160;</dt>
		<dd>Create new entries</dd>
		<dt>Delete&#160;</dt><dd> Delete existing entries</dd>
		<dt>Edit&#160;</dt><dd> Edit existing entries</dd>
		<dt>Edit State&#160;</dt><dd> Change an entry's state (enabled/disabled).</dd>
	</dl>

	<p>There are two very important points to understand from this screen. The first is to see how the permissions can be inherited from the parent Role. The second is to see how you can control the default permissions by Role and by Action.
	This provides a lot of flexibility. For example, if you wanted Shop Suppliers to be able to have the ability to create an article about their product, you could just change their Create value to "Allowed". If you wanted to not allow members of Administrator role to delete objects or change their state, you would change their permissions in these columns to Inherited (or Denied).
	It is also important to understand that the ability to have child roles is completely optional. It allows you to save some time when setting up new roles. However, if you like, you can set up all roles to have Public as the parent and not inherit any permissions from a parent role.</p>
@endif
