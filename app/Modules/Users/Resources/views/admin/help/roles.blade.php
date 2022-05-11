
	<h1>User Manager: Roles</h1>

	<ol class="toc">
		<li>
			<a href="#description">Managing Roles</a>
			<ol>
				<li><a href="#description">Description</a></li>
				<li><a href="#column-headers">Column Headers</a></li>
				<li><a href="#filters">List Filters</a></li>
				<li><a href="#toolbar">Toolbar</a></li>
				<li><a href="#tips">Quick Tips</a></li>
				<li><a href="#related">Related information</a></li>
			</ol>
		</li>
		<li>
			<a href="#column-headers">Overview of ACL</a>
			<ol>
				<li><a href="#how-permissions-work">How Permissions Work</a></li>
				<li><a href="#access-examples">View Access Levels Examples</a></li>
			</ol>
		</li>
	</ol>

	<h2 id="description">Description</h2>
	<p>User Roles control what actions a user may take on the site and which objects a user can view. This screen allows you to create, view, edit, and delete User Roles.</p>

	<h2 id="column-headers">Column Headers</h2>
	<p>In the table containing the users from your site, you will see different columns. Here you can read what they mean and what is displayed in that column.</p>

	<dl>
		<dt>Checkbox</dt><dd>Check this box to select one or more items. To select all items, check the box in the column heading. After one or more boxes are checked, click a toolbar button to take an action on the selected item or items. Many toolbar actions, such as Publish and Unpublish, can work with multiple items. Others, such as Edit, only work on one item at a time. If multiple items are checked and you press Edit, the first item will be opened for editing.</dd>
		<dt>Role Title</dt><dd>The name of the role.</dd>
		<dt>Users in Role</dt><dd>The number of users in this role.</dd>
	</dl>

	<h2 id="toolbar">Toolbar</h2>

	<p>At the top right you will see the toolbar:</p>

	<dl>
		<dt>Save</dt><dd>Save current settings.</dd>
		<dt>Cancel</dt><dd>Cancel any changes and revert to saved settings.</dd>
		<dt>New</dt><dd>Opens the editing screen to create a new user.</dd>
		<dt>Delete</dt><dd>Deletes the selected users. <strong>Requires selecting one or more records</strong>.</dd>
		<dt>Help</dt><dd>Opens this help screen.</dd>
	</dl>

	<h2 id="filters">List Filters</h2>
	<p>Below the list of records are the pagination controls. When the number of items is more than one page, you will see a page control bar as shown below.</p>

	<dl>
		<dt>Display #</dt><dd>Select the number of items to show on one page.</dd>
		<dt>Prev</dt><dd>Click to go to the previous page.</dd>
		<dt>Page numbers</dt><dd>Click to go to the desired page.</dd>
		<dt>Next</dt><dd>Click to go to the next page.</dd>
	</dl>

	<h2 id="tips">Quick Tips</h2>
	<ul>
		<li>Click on the name of a role to edit the role's properties.</li>
	</ul>

	@include('users::admin.help.acl')
