
	<h1>User Manager: Users</h1>

	<ol class="toc">
		<li><a href="#description">Description</a></li>
		<li><a href="#column-headers">Column Headers</a></li>
		<li><a href="#filters">List Filters</a></li>
		<li><a href="#toolbar">Toolbar</a></li>
		@if (auth()->user()->can('admin users'))
		<li><a href="#options">Options</a>
			<ol>
				<li><a href="#module">Module</a>
				<li><a href="#permissions">Permissions</a>
			</ol>
		</li>
		@endif
		<li><a href="#tips">Quick Tips</a></li>
		<li><a href="#related">Related information</a></li>
	</ol>

	<h2 id="description">Description</h2>
	<p>In this screen you have the ability to look at a list of your users and sort them in different ways. You can also edit and create users.</p>

	<h2 id="column-headers">Column Headers</h2>
	<p>In the table containing the users from your site, you will see different columns. Here you can read what they mean and what is displayed in that column.</p>
	<dl>
		<dt>Checkbox</dt><dd>Check this box to select one or more items. To select all items, check the box in the column heading. After one or more boxes are checked, click a toolbar button to take an action on the selected item or items. Many toolbar actions, such as Publish and Unpublish, can work with multiple items. <strong>Note: Some toolbar buttons will not appear until one or more rows are selected.</strong></dd>
		<dt>ID</dt><dd>This is a unique identification number for this item assigned automatically by the CMS. It is used to identify the item internally, and you cannot change this number. When creating a new item, this field displays 0 until you save the new entry, at which point a new ID is assigned to it.</dd>
		<dt>Name</dt><dd>The full name of the user.</dd>
		<dt>Username</dt><dd>The name the user will log in as.</dd>
		<dt>Roles</dt><dd>The list of roles that the user belongs to. Note that a user may belong to more than one role.</dd>
		<dt>Status</dt><dd>Whether or not the user is enabled.</dd>
		<dt>Last Visit</dt><dd>Here you can see the date on which the user last logged in.</dd>
	</dl>

	<h2 id="filters">List Filters</h2>

	<p><strong>Search Users</strong> - In the upper left is a Search field where users can be found by name, username, email, or ID.</p>

	<p>In the upper right, above the column headings, are three drop-down list boxes as shown below.</p>

	<dl>
		<dt>State</dt><dd>Select the desired state (Enabled or Disabled) to limit the list based on state. Select "- State -" to list Enabled and Disabled users.</dd>
		<dt>Role</dt><dd>Select a role from the list box to list only users who are members of that role. Select "- Role -" to select users regardless of role.</dd>
		<dt>Registration Date</dt><dd>Allows the users to be filtered by those who registered within certain roles of time.</dd>
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
		@if (auth()->user()->can('admin users'))
		<dt>Options</dt><dd>Opens the Options window where settings such as default parameters or permissions can be edited.</dd>
		@endif
		<dt>Help</dt><dd>Opens this help screen.</dd>
	</dl>

@if (auth()->user()->can('admin users'))
	<h2 id="options">Options</h2>
	<p>Click the Options button to open the <strong>User Manager Options</strong> window which lets you configure this component.

	<h3 id="common-buttons">Buttons Common to All Tabs</h3>
	<dl>
		<dt>Save</dt><dd>Saves the user options and stays in the current screen.</dd>
		<dt>Save &amp; Close</dt><dd>Saves the user options and closes the current screen.</dd>
		<dt>Cancel/Close</dt><dd>Closes the current screen and returns to the previous screen without saving any modifications you may have made.</dd>
	</dl>

	<h3 id="module">Module</h3>
	<dl>
		<dt>Allow User Registration.</dt><dd>(Yes/No) If set Yes, users can register from the front end of the site using the Create an Account link provided on the Login module. If set to No, the "Create and Account" link will not show.</dd>
		<dt>Default User Role.</dt><dd>The role that users are assigned to by default when they register on the site. Defaults to Registered.</dd>
		<dt>Guest User Role.</dt><dd>The role that guests are assigned to. (Guests are visitors to the site who are not logged in.) This is set to Public by default. If you change this to a different role, it is possible to create content on the site that is visible to guests but not visible to logged in users.</dd>
		<dt>Allow users to delete their accounts.</dt><dd>(Yes/No) If set Yes, users will see a section on their account page allowing them to delete their account and all associated data. This process warns the user and forces them to confirm their choice.</dd>
		<dt>Profile Photos.</dt><dd>(Yes/No) If set Yes, user profile photos will be displayed in places throughout the site, such as on their account page.</dd>
		<dt>Terms of Service page.</dt><dd>Select a content page that contains the Terms of Service that must be agreed to for registration. If no page is selected, the ToS agreement will not be displayed or required for registration.</dd>
	</dl>

	<h3 id="permissions">Permissions</h3>

	<p>This screen allows you to set the component permissions. This is important to consider if you have sites with many different user categories all of whom need to have different accessibilities to the component. The screenshot below describes what you should see and the text below that describes what each permission level gives the user access to:</p>

	<p>You work on one Role at a time by opening the slider for that role. You change the permissions in the Select New Settings drop-down list boxes.
	The options for each value are Inherited, Allowed, or Denied. The Calculated Setting column shows you the setting in effect. It is either Not Allowed (the default), Allowed, or Denied.
	Note that the Calculated Setting column is not updated until you press the Save button in the toolbar. To check that the settings are what you want, press the Save button and check the Calculated Settings column.</p>

	<!-- <p>The default values used here are the ones set in the <a href="#" title="Help25:Site Global Configuration">Global Configuration Permissions Tab</a></p> -->

	<dl>
		<dt>Access Administration Interface</dt>
		<dd>Open the Users Manger screens</dd>
		<dt>Configure</dt>
		<dd>Open the Users Manager options screen (the modal window these options are in)</dd>
		<dt>Create&#160;</dt>
		<dd>Create new users in the component</dd>
		<dt>Delete&#160;</dt><dd> Delete existing users in the component</dd>
		<dt>Edit&#160;</dt><dd> Edit existing users in the component</dd>
		<dt>Edit State&#160;</dt><dd> Change a user's state (enabled/disabled).</dd>
	</dl>

	<p>There are two very important points to understand from this screen. The first is to see how the permissions can be inherited from the parent Role. The second is to see how you can control the default permissions by Role and by Action.
	This provides a lot of flexibility. For example, if you wanted Shop Suppliers to be able to have the ability to create an article about their product, you could just change their Create value to "Allowed". If you wanted to not allow members of Administrator role to delete objects or change their state, you would change their permissions in these columns to Inherited (or Denied).
	It is also important to understand that the ability to have child roles is completely optional. It allows you to save some time when setting up new roles. However, if you like, you can set up all roles to have Public as the parent and not inherit any permissions from a parent role.</p>
@endif

	<h2 id="tips">Quick Tips</h2>
	<ul>
		<li>Click on the name of a user to view the user's properties.</li>
		<li>Click on the Column Headers to sort the users by that column, ascending or descending.</li>
	</ul>
	<!--
	<h2 id="related">Related information</h2>
	<ul>
		<li><a href="#" title="Help25:Users User Manager Edit">User Manager: Add/Edit User</a></li>
		<li><a href="#" title="Help25:Users Roles">User Manager: Roles</a></li>
		<li><a href="#" title="Help25:Users Access Levels">User Manager: Access Levels</a></li>
		<li><a href="#" title="Access Control List Tutorial" class="mw-redirect">ACL Tutorial</a></li>
	</ul>
	-->
