
	<h1>Widget Manager</h1>

	<ol class="toc">
		<li><a href="#description">Description</a></li>
		<li><a href="#column-headers">Column Headers</a></li>
		<li><a href="#filters">List Filters</a></li>
		<li><a href="#toolbar">Toolbar</a></li>
		@if (auth()->user()->can('admin widgets'))
		<li><a href="#options">Options</a></li>
		@endif
	</ol>

	<h2 id="description">Description</h2>
	<p>The Widget Manager is where you add and edit Widgets. Widgets are used to display content and/or media around the main content.</p>

	<ol>
		<li>All sites require at least 1 Menu Widget</li>
		<li>All other widget types are optional. (Examples: News, Banner, Notice)</li>
		<li>Every Menu is accompanied by a menu widget.</li>
		<li>Widgets can have multiple occurrences.</li>
		<li>Some Widgets are linked to widgets. For example, each Menu Widget is related to a Menu in the Menu Manager widget. To define a Menu, you need to create the Menu and Menu Items using the Menu Manager and then create the Widget for the Menu using this screen. Other Widgets, such as Custom HTML and Breadcrumbs, do not depend on any other content.</li>
	</ol>

	<h2 id="column-headers">Column Headers</h2>
	<p>In the table containing the users from your site, you will see different columns. Here you can read what they mean and what is displayed in that column.</p>
	<dl>
		<dt>Checkbox</dt><dd>Check this box to select one or more items. To select all items, check the box in the column heading. After one or more boxes are checked, click a toolbar button to take an action on the selected item or items. Many toolbar actions, such as Publish and Unpublish, can work with multiple items. <strong>Note: Some toolbar buttons will not appear until one or more rows are selected.</strong></dd>
		<dt>ID</dt><dd>This is a unique identification number for this item assigned automatically by the CMS. It is used to identify the item internally, and you cannot change this number. When creating a new item, this field displays 0 until you save the new entry, at which point a new ID is assigned to it.</dd>
		<dt>Title</dt><dd>The title of the widget instance. You can click on the title to edit.</dd>
		<dt>State</dt><dd>State of the item. Possible values are:
			<ul>
				<li><i>Published</i>: The item is published. This is the only state that will allow regular website users to view this item.</li>
				<li><i>Unpublished</i>: The item is unpublished.</li>
				<li><i>Trashed</i>: The item has been sent to the Trash.</li>
			</ul>
		</dd>
		<dt>Position</dt><dd>The position on the page where this widget is displayed. Positions are locations on the page where widgets can be placed (for example, "left" or "right"). Positions are defined in the Theme in use for the page. Positions can also be used to insert a widget inside a Page using the syntax <code>&#64;widget(xxx)</code>, where "xxx" is a unique position for the widget.</dd>
		<dt>Ordering</dt><dd>The order to display widgets within a Position. If the list is sorted by this column, you can change the display order of widgets within a Position by sorting entries by Ordering and then clicking the arrows.</dd>
		<dt>Widget</dt><dd>The system name of the widget. Many Extensions contribute additional widgets.</dd>
		<dt>Pages</dt><dd>The Menu Items where this widget will be displayed.</dd>
		<dt>Access Level</dt><dd>Who has access to this item.</dd>
	</dl>

	<h2 id="filters">List Filters</h2>

	<p><strong>Search</strong> - In the upper left is a Search field where entries can be found by title, note, or ID.</p>

	<p>In the upper right, above the column headings, are multiple drop-down list boxes as shown below.</p>

	<dl>
		<dt>Type</dt><dd>Show only widgets for the specific area of the site (front-end vs admin).</dd>
		<dt>State</dt><dd>Select the desired state to limit the list based on state.</dd>
		<dt>Position</dt><dd>Select a Position from the drop-down list box of available Positions.</dd>
		<dt>Widget</dt><dd>Select the Widget Type from the drop-down list box of available Widget Types. Additional ones may be available if you have installed any Extensions.</dd>
		<dt>Access</dt><dd>Lets you show only items that have a specified viewing access level. The list box will show the access levels defined for your site.</dd>
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
		<dt>New</dt><dd>Opens the editing screen to create a new widget instance.</dd>
		<dt>Publish</dt><dd>Makes the selected widgets available to visitors to your website.</dd>
		<dt>Unpublish</dt><dd>Makes the selected widgets unavailable to visitors to your website.</dd>
		<dt>Check In</dt><dd>Checks-in the selected widgets. Works with one or multiple widgets selected. When editing an item, it's marked as "checked-out" to avoid conflicts with multiple people editing the same record at the same time.</dd>
		<dt>Delete</dt><dd>Deletes the selected entries. <strong>Requires selecting one or more records</strong>.</dd>
		@if (auth()->user()->can('admin users'))
		<dt>Options</dt><dd>Opens the Options window where settings such as default parameters or permissions can be edited.</dd>
		@endif
		<dt>Help</dt><dd>Opens this help screen.</dd>
	</dl>

@if (auth()->user()->can('admin widgets'))
	<h2 id="options">Options</h2>
	<p>Click the Options button to open the <strong>Widget Manager Options</strong> window which lets you configure this module.</p>

	<h3 id="common-buttons">Buttons Common to All Tabs</h3>
	<dl>
		<dt>Save</dt><dd>Saves the user options and stays in the current screen.</dd>
		<dt>Cancel/Close</dt><dd>Closes the current screen and returns to the previous screen without saving any modifications you may have made.</dd>
	</dl>

	<h3 id="permissions">Permissions</h3>

	<p>This screen allows you to set the module permissions. This is important to consider if you have sites with many different user categories all of whom need to have different accessibilities to the module. The screenshot below describes what you should see and the text below that describes what each permission level gives the user access to:</p>

	<p>You work on one Role at a time by opening the slider for that role. You change the permissions in the Select New Settings drop-down list boxes.
	The options for each value are <code>Inherited</code>, <code>Allowed</code>, or <code>Denied</code>. The Calculated Setting column shows you the setting in effect. It is either <span class="badge badge-warning">Not Allowed (the default)</span>, <span class="badge badge-success">Allowed</span>, or  <span class="badge badge-danger">Denied</span>.
	Note that the Calculated Setting column is not updated until you press the Save button in the toolbar. To check that the settings are what you want, press the Save button and check the Calculated Settings column.</p>

	<dl>
		<dt>Access Administration Interface</dt>
		<dd>Open the Widget Manger screens</dd>
		<dt>Configure</dt>
		<dd>Open the options screen (the modal window these options are in)</dd>
		<dt>Create</dt><dd>Create new widget instances</dd>
		<dt>Delete</dt><dd>Delete existing entries</dd>
		<dt>Edit</dt><dd>Edit existing entries</dd>
		<dt>Edit State</dt><dd>Change an entry's state (enabled/disabled).</dd>
	</dl>

	<p>There are two very important points to understand from this screen. The first is to see how the permissions can be inherited from the parent Role. The second is to see how you can control the default permissions by Role and by Action.
	This provides a lot of flexibility. For example, if you wanted Shop Suppliers to be able to have the ability to create an article about their product, you could just change their Create value to "Allowed". If you wanted to not allow members of Administrator role to delete objects or change their state, you would change their permissions in these columns to Inherited (or Denied).
	It is also important to understand that the ability to have child roles is completely optional. It allows you to save some time when setting up new roles. However, if you like, you can set up all roles to have Public as the parent and not inherit any permissions from a parent role.</p>
@endif
