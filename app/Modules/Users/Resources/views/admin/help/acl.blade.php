<h1><span>Overview of ACL</h1>

<p>The ACL system can be thought of as being divided into two completely separate systems. One system controls what things on the site users can <i>view</i>. The other controls what things users can <i>do</i> (what actions a user can take). The ACL for each is set up differently.</p>

<section>
	<h2 id="what-users-can-see">Controlling What Users Can See</h2>
	<p>The setup for controlling what users can see is done as follows:</p>
	<ol>
		<li>Create the different User Roles required for the site. Each Role can be thought of as a role that a user will have for the site. Keep in mind that one User can be a member of one or more Roles. If desired, roles can have parent roles. In this case, they automatically inherit the Access Levels of the parent role.</li>
		<li>Create the set of Access Levels required for the site. This could be a small number or a large number depending on the number of different roles and how many categories of items there are. Assign each Access Level to one or more of the User Roles created in step 1.</li>
		<li>Assign each item to be viewed to one Access Level. Items include content items (articles, contacts, and so on), menu items, and modules.</li>
	</ol>
	<p>Any time a user is about to view an item on a page, the program checks whether the user has access to the item, as follows:</p>
	<ol>
		<li>Creates a list of all the Access Levels that the User has access to, based on all Roles that the User belongs to. Also, if a role has a parent role, access levels for the parent role are also included in the list.</li>
		<li>Checks whether the Access Level for the item (article, module, menu item, and so on) is on that list. If yes, then the item is displayed to the user. If no, then the item is not displayed.</li>
	</ol>
	<p>Note that Access Levels are set separately for each Role and are not inherited from a role's parent role.</p>

	<h2 id="what-users-can-do">Controlling What Users Can Do</h2>
	<p>The system for setting up what users can do -- what actions they can take on a given item -- is set up with the Permissions tab of Global Configuration and the Permissions tab of the Options screen of each module. Permissions can also be set up at the Category level for core modules and at the Article level for articles.</p>
	<p>Note that this set up is independent of the setup for viewing.</p>
	<p>When a user wants to initiate a specific action against a module item (for example, edit an article), the system checks the permission for this combination of user, item, and action. If it is allowed, then the user can proceed. Otherwise, the action is not allowed.</p>
	<p>The remainder of this tutorial discusses how we control what users can do -- what action permissions they have.</p>
</section>
<section>
	<h1 id="actions-roles-inheritance">Actions, Roles, and Inheritance</h1>
	<p>The other side of ACL is granting permissions to users to take actions on objects. Here again there is a big change between Version 1.5 and 1.6. In 1.5, the actions allowed for a given role were fixed. For example, a User in the Author role could only submit an article whereas someone in the Publisher role could submit, edit, and publish articles. Also, in version 1.5 the permissions were all-or-nothing. A member of the Editor role could edit all articles on the site.</p>

	<h2 id="how-permissions-work">How Permissions Work</h2>
	<p>There are four possible permissions for actions, as outlined below:</p>
	<dl>
		<dt>Not set</dt><dd>Defaults to "deny" but, unlike the Deny permission, this permission can be overridden by setting a child role or a lower level in the permission hierarchy to "Allow". This permission only applies to the Global Configuration permissions.</dd>
		<dt>Inherit</dt><dd>Inherits the value from a parent Role or from a higher level in the permission hierarchy. This permission applies to all levels except the Global Configuration level.</dd>
		<dt>Deny</dt><dd>Denies this action for this level and role. <strong>IMPORTANT:</strong> This also denies this action for all child roles and all lower levels in the permission hierarchy. Putting in Allow for a child role or a lower level will not have any effect. The action will always be denied for any child role member and for any lower level in the permission hierarchy.</dd>
		<dt>Allow</dt><dd>Allows this action for this level and role and for lower levels and child roles. This does not have any effect if a higher role or level is set to Deny or Allow. If a higher role or level is set to Deny, then this permission will always be denied. If a higher role or level is set to Allow, then this permission will already be allowed.</dd>
	</dl>

	<h2 id="permission-hierarchy-levels">Permission Hierarchy Levels</h2>
	<p>Action permissions can be defined at up to four levels, as follows:</p>
	<ol>
		<li><b>Global Configuration</b>: determines the default permissions for each action and role.</li>
		<li><b>Module Options-&gt;Permissions</b>: can override the default permissions for this module (for example, Pages, Menus, Users, and so on)</li>
		<li><b>Object</b>: Can override the permissions for a specific object in a module, such as a content page or news article. This level only applies to that object under its parent module. Other modules only allow the first two levels.</li>
	</ol>

	<h3 id="global-config">Global Configuration</h3>
	<p>This is accessed from Site → Global Configuration → Permissions. This screen allows you set the top-level permission for each role for each action, as shown in the screenshot below.</p>

	<p>The options for each value are Inherited, Allowed, or Denied. The Calculated Setting column shows you the setting in effect. It is either Not Allowed (the default), Allowed, or Denied.</p>
	<p>You work on one Role at a time by opening the slider for that role. You change the permissions in the Select New Settings drop-down list boxes.</p>
	<p>Note that the Calculated Setting column is not updated until you press the Save button in the toolbar. To check that the settings are what you want, press the Save button and check the Calculated Settings column.</p>

	<h3 id="module-options">Module Options-&gt;Permissions</h3>
	<p>This is accessed for each module by clicking the Options icon in the toolbar. This screen is similar to the Global Configuration screen above. For example, clicking the Options toolbar icon in the Menu Manager shows the Menus Configuration below.</p>

	<p>Access to Options is only available to members of roles who have permission for the Configure action in for each module. In the example above, the Administrator role has Allowed permission for the Configure option, so members of this role can access this screen.</p>

	<h2 id="access-levels">Access Levels</h2>
	<p>Access Levels in 2.5 series are simple and flexible. The screen below shows the Special Access Level.</p>

	<p>Simply check the box for each role you want included in that level. The Special Access Level includes the Manager, Author, and Super Users roles. It also includes child roles of those roles. So, Administrator role is included, since it is a child role of the Manager role. The Editor, Publisher, and Shop Suppliers roles are included, since they are child roles of Author. (Note that we could check all of the child roles if we wanted and it wouldn't hurt anything.)</p>
	<p>Once Access Levels are created, they are used in the same way as in version 1.5. Each object in the front end is assigned an Access Level. If the level is Public, then anyone may access that object. Otherwise, only members of roles assigned to that access level may access that object. Access levels are assigned to Menu Items and to Modules. Each one can only be assigned to one access level.</p>
	<p>For example, the screen below shows the Edit Menu Item screen with the list of available access levels.</p>
</section>
<section>
	<h1 id="default-acl">Default ACL Setup</h1>
	<p>When the CMS is installed, these are set to their initial default settings. We will discuss these initial settings as a way to understand how the ACL works.</p>

	<h2 id="default-roles">Default Roles</h2>
	<p>Version 2.5 allows you to define your own Roles. When you install version 2.5, it includes a set of default roles, as shown below.</p>

	<p>The arrows indicate the child-parent relationships. As discussed above, when you set a permission for a parent role, this permission is automatically inherited by all child roles. The Inherited, and Allowed permissions can be overridden for a child role. The Denied permission cannot be overridden and will always deny an action for all child roles.</p>

	<h2 id="global-config2">Global Configuration</h2>
	<p>As discussed earlier, the permissions for each action are inherited from the level above in the permission hierarchy and from a role's parent role. Let's see how this works. The top level for this is the entire site. This is set up in the Site-&gt;Global Configuration-&gt;Permissions, as shown below.</p>

	<p>The first thing to notice are the nine Actions: Site Login, Admin Login, Super Admin, Access Module, Create, Delete, Edit, Edit State. and Edit Own. These are the actions that a user can perform on an object in the CMS. The specific meaning of each action depends on the context. For the Global Configuration screen, they are defined as follows:</p>
	<dl>
	<dt>Site Login&#160;</dt>
	<dd>Login to the front end of the site</dd>
	</dl>
	<dl>
	<dt>Admin Login&#160;</dt>
	<dd>Login to the back end of the site</dd>
	</dl>
	<dl>
	<dt>Super Admin&#160;</dt>
	<dd>Grants the user "super user" status. Users with this permission can do anything on the site. Only users with this permission can change Global Configuration settings (this screen). These permissions cannot be restricted. It is important to understand that, if a user is a member of a Super Admin role, any other permissions assigned to this user are irrelevant. The user can do any action on the site. However, Access Levels can still be assigned to control what this role sees on the site. (Obviously, a Super Admin user can change Access Levels if they want to, so Access Levels do not totally restrict what a Super Admin user can see.)</dd>
	</dl>
	<dl>
	<dt>Access Module</dt>
	<dd>Open the module manager screens (User Manager, Menu Manager, Article Manager, and so on)</dd>
	</dl>
	<dl>
	<dt>Create&#160;</dt>
	<dd>Create new objects (for example, users, menu items, articles, weblinks, and so on)</dd>
	</dl>
	<dl>
	<dt>Delete&#160;</dt>
	<dd>Delete existing objects</dd>
	</dl>
	<dl>
	<dt>Edit&#160;</dt>
	<dd>Edit existing objects</dd>
	</dl>
	<dl>
	<dt>Edit State&#160;</dt>
	<dd>Change object state (Publish, Unpublish, Archive, and Trash)</dd>
	</dl>
	<dl>
	<dt>Edit Own&#160;</dt>
	<dd>Edit objects that you have created.</dd>
	</dl>
	<p>Each Role for the site has its own slider which is opened by clicking on the role name. In this case (with the sample data installed), we have the standard 7 roles that we had in version 1.5 plus two additional roles called "Shop Suppliers" and "Customer Role". Notice that our roles are set up with the same permissions as they had in version 1.5. Keep in mind that we can change any of these permissions to make the security work the way we want. Let's go through this to see how it works.</p>
	<ul>
	<li><b>Public</b> has everything set to "Not set", as shown below.</li>
	</ul>

	<dl>
	<dd>This can be a bit confusing. Basically, "Not Set" is the same as "Inherited". Because Public is our top-level role, and because Global Configuration is the top level of the module hierarchy, there is nothing to inherit from. So "Not Set" is used instead of "Inherit".</dd>
	<dd>The default in this case is for no permissions. So, as you would expect, the Public role has no special permissions. Also, it is important to note that, since nothing is set to Denied, all of these permissions may be overridden by child roles or by lower levels in the permission hierarchy.</dd>
	</dl>
	<ul>
	<li><b>Manager</b> is a "child" role of the Public role. It has Allowed permissions for everything except Access Module and Super Admin. So a member of this role can do everything in the front and back end of the site except change Global Permissions and Module Options.</li>
	</ul>
	<ul>
	<li><b>Administrator</b> role members inherit all of the Manager permissions and also have Allowed for Access Module. So members of this role by default can access the Options screens for each module.</li>
	</ul>
	<ul>
	<li><b>Registered</b> is the same a Public except for the Allow permission for the Site Login action. This means that members of the Registered role can login to the site. Since default permissions are inherited, this means that, unless a child role overrides this permission, all child roles of the Registered role will be able to login as well.</li>
	</ul>
	<ul>
	<li><b>Author</b> is a child of the Registered role and inherits its permissions and also adds Create and Edit Own. Since Author, Editor, and Publisher have no back-end permissions, we will discuss them below, when we discuss front-end permissions.</li>
	</ul>
	<ul>
	<li><b>Editor</b> is a child of the Authors role and adds the Edit permission.</li>
	</ul>
	<ul>
	<li><b>Publisher</b> is a child of Editor and adds the Edit State permission.</li>
	</ul>
	<ul>
	<li><b>Shop Suppliers</b> is an example role that is installed if you install the sample data. It is a child role of Author.</li>
	</ul>
	<ul>
	<li><b>Customer Role</b> is an example role that is installed if you install the sample data. It is a child role of Registered.</li>
	</ul>
	<ul>
	<li><b>Super Users</b> role has the Allow permission for the Super Admin action. Because of this, members of this role have super user permissions throughout the site. They are the only users who can access and edit values on the Global Configuration screen. Users with permission for the Super Admin action have some special characteristics:
	<ul>
	<li>If a user has Super Admin permissions, no other permissions for this user matter. The user can perform any action on the site.</li>
	<li>Only Super Admin users can create, edit, or delete other Super Admin users or roles.</li>
	</ul>
	</li>
	</ul>
	<p>There are two very important points to understand from this screen. The first is to see how the permissions can be inherited from the parent Role. The second is to see how you can control the default permissions by Role and by Action.</p>
	<p>This provides a lot of flexibility. For example, if you wanted Shop Suppliers to be able to have the ability to login to the back end, you could just change their Admin Login value to "Allowed". If you wanted to not allow members of Administrator role to delete objects or change their state, you would change their permissions in these columns to Inherited (or Denied).</p>
	<p>It is also important to understand that the ability to have child roles is completely optional. It allows you to save some time when setting up new roles. However, if you like, you can set up all roles to have Public as the parent and not inherit any permissions from a parent role.</p>

	<h2 id="Module_Options_.26_Permissions">Module Options &amp; Permissions</h2>
	<p>Now, let's continue to see how the default back-end permissions for version 2.5 mimic the permissions for version 1.5. The Super Users role in 2.5 is equivalent to the Super Administrator role in 1.5.</p>
	<p>Just looking at the Global Configuration screen above, it would appear that the Administrator role and the Manager role have identical permissions. However, in version 1.5 Administrators can do everything except Global Configuration, whereas Managers are not permitted to add users or work with menu items. That is also true in the default version 2.5 configuration. Let's see how this is accomplished.</p>
	<p>If we navigate to Users-&gt;User Manager and click the Options button in the toolbar, we see the screen below:</p>

	<p>This screen is the same as the Global Configuration Permissions screen, except that these values only affect working with Users. Let's look at how this works.</p>
	<p>First, notice that the Administrator role has Allow permission for the Admin action and the Manager role has Deny permission for this action. Remember that the Admin action in the Global Configuration screen gives the role "super user" permissions. In this screen, the Admin action allows you to edit the Options values. So, the Administrator role can do this but the Manager role cannot.</p>
	<p>Next, notice that the Administrator has Inherit for the Manage action and the Manager role has Deny permission. In this screen, the Manage action gives a role access to the User Manager. Since the Administrator has Allow for the Manage action by default, then the Inherit permission here means they inherit the Allow permission for the Manage action. Since the Manager role has Deny permission for the Manage action, members of the Manager role cannot access the User Manager and therefore cannot do any of the other user-related actions.</p>
	<p>If you look at the Options for Menus-&gt;Menu Manager, you will see the same default settings as for the User Manager. Again, the Administrator role can manage and set default permissions for Menu Manager objects whereas the Manager role cannot.</p>
	<p>In short, we can see that the different permissions for the Administrator and Manager roles are set using the Options-&gt;Permissions forms on the User Manager and Menu Manager screens.</p>
	<p>It is also important to understand that this same Options-&gt;Permissions form for setting default permissions is available for all objects, including Media Manager, Banners, Contacts, Newsfeeds, Redirect, Search Statistics, Web Links, Extensions, Modules, Plugins, Templates, and Language. So you now have the option to create user roles with fine-tuned sets of back-end permissions.</p>

	<h2 id="Front_End_Permissions">Front End Permissions</h2>
	<p>Default permissions for the front end are also set using the Options form. Let's look at Content-&gt;Article Manager-&gt;Options-&gt;Permissions. First, let's look at the permissions for Manager, as shown below.</p>

	<p>Manager has allowed permission for all actions except Configure. So members of the Manager role can do everything with Articles except open the Options screen.</p>
	<p>Now let's look at Administrator, as shown below.</p>

	<p>Administrator has Allowed for Configure, so Administrators can edit this Options screen.</p>
	<p>Both roles can create, delete, edit, and change the state of articles.</p>
	<p>Now, let's look at the roles Publisher, Editor, and Author and see how their permissions are set.</p>
	<p>Authors only have Create and Edit Own permissions, as shown below.</p>

	<p>This means that Authors can create articles and can edit articles they have created. They may not delete articles, change the published state of articles, or edit articles created by others.</p>
	<p>Editors have the same permissions as Authors with the addition of permission for the Edit action, as shown below.</p>

	<p>So Editors can edit articles written by anyone.</p>
	<p>Publishers can do everything Editors can do plus they have permission for the Edit State action, as shown below.</p>

	<p>So Publishers can change the published state of an article. The possible states include Published, Unpublished, Archived, and Trashed.</p>
	<p>All of these roles have Inherit permission for Configure and Access Module. Remember that Author is a child of the Registered role, and Registered does not have any default permissions except for Login. Since Registered does not have permission for Configure and Access Module, and since Author's permission for these actions is "Inherited", then Author does not have these permissions either. This same permission is passed from Author to Editor and from Editor to Publisher. So, by default, none of these roles are allowed to work with articles in the back end.</p>
	<p>It is important to remember that these permissions are only default settings for categories and articles and for any child roles that are created. So they can be overridden for child roles, for categories, and for specific articles.</p>
	<p>Also, note that there are no Denied permissions for any actions in the default settings. This allows you to add Allowed permissions at any level. Remember, once you have an action set for Denied, this action will be denied at all lower levels in the hierarchy. For example, if you set the Admin Login for Registered to Denied (instead of Inherited), you could not grant Publishers Allowed permissions for this action.</p>

	<h2 id="Article_Manager_.26_Actions_Diagram">Article Manager &amp; Actions Diagram</h2>
	<p>The diagram below shows how each action in the permissions form relates to the various options on the Article Manager screen.</p>

	<ul>
	<li><b>Configure</b> allows you to view and change the Options for the module.</li>
	<li><b>Access Module</b> allows you to navigate to the Article Manager. Without this permission, no other actions are possible.</li>
	<li><b>Create</b> allows you to add new articles.</li>
	<li><b>Delete</b> allows you to delete trashed articles. Note that the Delete icon only shows in the toolbar when you have the "Select State" filter set to "Trash".</li>
	<li><b>Edit</b> allows you to edit existing articles.</li>
	<li><b>Edit State</b> allows to you Publish, Unpublish, Archive, or Trash articles.</li>
	<li><b>Edit Own</b> is the same as Edit except that it only applies to articles written by you.</li>
	</ul>

	<h1 id="Allowing_Guest-Only_Access_to_Menu_Items_and_Modules">Allowing Guest-Only Access to Menu Items and Modules</h1>
	<p>Version 1.6 introduced the ability to create a View Access Level that is only for guests of the site (meaning a user who is not logged in). The example below shows how you can set up this new feature.</p>
	<ol>
	<li>Create a new user role called Guest. Make it a child of the Public role as shown below.

	</li>
	<li>Create a new access level called Guest and grant only the Guest role access to this level, as shown below.

	</li>
	<li>Navigate to User Manager→Options→Module and change the Guest User Role from the default value of "Public" to "Guest", as shown below.</li>
	</ol>

	<p>Now, if we assign a menu item, module, or other object to the Guest access level, only non-logged in users will have access. For example, if we create a new menu item with access level of Guest, as shown below,</p>

	<p>this menu item will only be visible to non-logged-in visitors to the site.</p>
	<p>If required other user roles like Author can be granted access in the Guest access level, this would allow Authors to view articles in the front end for editing.</p>
	<p><b>N.B. Login/logout in front end (<i>for changing data in session</i>) to see the change.</b></p>
</section>
<section>
	<h1 id="Using_Permission_and_Role_Levels_Together">Using Permission and Role Levels Together</h1>
	<p>As discussed above, it is possible to define roles in a hierarchy, where each child role inherits action permissions (for example, the create permission) from its parent role. Action permissions are also be inherited from the permission level above. For example, a permission in the Article Manager is inherited from the same permission in the Global Configuration, and a permission in a child Category is inherited from the parent Category permission.</p>
	<p>This dual inheritance can be confusing, but it can also be useful. Let's consider an example as follows. We have a school with a role hierarchy of Teachers → History Teachers → Assistant History Teachers. We also have a category hierarchy of Assignments → History Assignments. We want History Teachers and Assistant History Teachers to have the following permissions:</p>
	<ul>
	<li>both roles can create new articles only in the History Assignments category.</li>
	</ul>
	<ul>
	<li>only History Teachers (not Assistant History Teachers) can Publish or otherwise have Edit State permission.</li>
	</ul>
	<p>This ACL scheme is very easy to implement. The diagram below shows how this would be set up for the Create Action.</p>

	<p>In the diagram, the Permission Hierarchy is shown down the left side and the Role hierarchy is shown across the top. Permissions are inherited down and to the right, as shown by the arrows. To implement the desired permissions, we leave the Global Configuration blank (Not Set) for all three roles. Similarly, in the Article Manager and Assignments Category, we leave the Create permission to Inherit for all the roles. As shown in the diagram, this means that these roles do not have Create permission for articles in general or for articles in the Assignments role.</p>
	<p>To sum up so far, we have not set any special permissions to get to this point. Now, in the History Assignments category permissions screen, we set the Create permission to Allow for the History Teachers role. This setting overrides the Soft (Implicit) Deny that we had by default and gives members of this role permission to create content (articles and child categories) for this category. This Allow setting also is inherited by the Assistant History Teachers role.</p>
	<p>Next, we need to grant History Teachers the Edit State permission while denying this permission to Assistant History Teachers. This is done as shown in the diagram below.</p>

	<p>This configuration is the same as the one above except that this time we set the Edit State permission in the History Assignments category to Deny for the Assistant History Teachers role. This means that Assistant History Teachers will not be able to Publish or Unpublish articles in this category.</p>
	<p>Note that this was accomplished by setting just two permissions in the History Assignments category: Allow for the History Teachers role and Deny for the Assistant History Teachers role.</p>
</section>
<section>
	<h1 id="ACL_Action_Permission_Examples">ACL Action Permission Examples</h1>
	<p>Here are some examples of how you might set up the ACL for some specific situations.</p>

	<h2 id="Back-end_Article_Administrator">Back-end Article Administrator</h2>
	<p><b>Problem:</b></p>

	<p>We want to create a role called "Article Administrator" with back-end permissions only for articles and not for any other back-end menu options. Members of this role should be able to use all of the features of the article manager, including setting article permissions.</p>
	<p><b>Solution:</b></p>

	<ol>
	<li>Create a new role called Article Administrator and make its parent role Public, as shown below.

	Because its parent role is Public, it won't have any permissions by default.</li>
	<li>In Users → Access Levels, edit the Special Access level to add the new role. That way they can get access to the back end menu items and modules (This assumes that the modules for the admin menu and quickicons have the Special Access level assigned to them, which is the default.)

	By default, the back-end menu items and modules are set to Special access, so if you forget to add the new role to the Special access level, you won't see any modules or menu items when you log in as a user of the new role.</li>
	<li>In Site → Global Configuration → Permissions, click on the Article Administrator role and change the permissions to Allowed for the following actions: Admin Login, Create, Delete, Edit, Edit State, and Edit Own. The screen below shows what will show before you press Save.

	After you save, the Calculated Permissions should show as shown below.

	Note that the permission for the Access Module is Inherited, which translates to Not Allowed. This is important. This means that this role will only be able to access modules if we give the role "Allowed" permission for Access Module. So we only have to change the one module we want to give them access to and don't have to change any settings for the modules where we don't want them to have access. If we had a case where we wanted to give a role access to everything except for one module, we could set the default to Allowed and then set the one module to Denied. Also note that we did not give the role Site Login permission, so users in this role will not be able to log into the front end. (If we wanted to allow that, we would just change the permission to Allowed for Site Login.)</li>
	<li>In Article Manager &rarr; Options &rarr; Permissions, change permissions to Allowed for this role for the Access Module action, as shown below.

	All of the other desired permissions are inherited.</li>
	</ol>

	<p>That's all you need to do. Members of this role can login to the back end and do everything in Article Manager but can't do anything else in the back end. For example, the screen below shows what a user in the Article Manager will see when they login to the back end.</p>
</section>
<section>
	<h1 id="ACL_View_Access_Levels_Examples">ACL View Access Levels Examples</h1>
	<p>A basic concept of using Access Levels is that all items with the same Access will be viewable by the same role of users. In other words, if two items have the same Access, you can't have one viewable by one user and not viewable by another user. On the other hand, it is easy to have one Role view any number of items with different Access levels.</p>
	<p>Similarly, each Role has exactly the same combination of Access levels, but one User can be a member of more than one role. Depending on the situation, you may want to have users only in one Role or you may need to have a User in more than one Role.</p>
	<p>This means that we may need to role our items so that items so that all items in a role have the same level of sensitivity. Here are some examples.</p>
</section>
<section>
	<h2 id="Hierarchical_Example">Hierarchical Example</h2>
	<p>In this example, Access levels are hierarchical, for example, like government security clearance codes. Say for example we have the following sets of classified documents: Classified, Secret, and Top Secret. Users have corresponding clearence codes. Users with Classified clearance can only see Classified documents and cannot see Secret or Top Secret. Users with Secret clearance can see Classified and Secret documents but not Top Secret. Users with Top Secret can see all documents.</p>
	<p>In this case, you would create three Access levels: Classified, Secret, and Top Secret and the same three Roles. Users would only be members of one role, as follows:</p>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th scope="col">User</th>
				<th scope="col">Role</th>
				<th scope="col">Access Levels</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>C1, C2, C3</td>
				<td>Classified</td>
				<td>Classified</td>
			</tr>
			<tr>
				<td>S1, S2, S3</td>
				<td>Secret</td>
				<td>Classified, Secret</td>
			</tr>
			<tr>
				<td>TS1, TS2, TS3</td>
				<td>Top Secret</td>
				<td>Classified, Secret, Top Secret</td>
			</tr>
		</tbody>
	</table>
	<p>In this case, all users are in exactly one role, but some roles have access to more than one Access Level of items. In other words, we have a one-to-one relationship between users and roles, but a one-to-many relationship between Roles and Access Levels.</p>
</section>
<section>
	<h2 id="Team_Security_Example">Team Security Example</h2>
	<p>Another possible use case is a set of non-hierarchical teams. Let's say we have three teams, T1, T2, and T3. Some users are only on one team, but others might be on two or more teams. In this case, we could set up our Access Levels and Roles by team. Documents for each team have the access level for that team, and the Role for the team has only the one access level. When a User is on more than one team, they get added to the role for each team, as follows:</p>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th scope="col">User</th>
				<th scope="col">Description</th>
				<th scope="col">Role</th>
				<th scope="col">Access Levels</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>U1</td>
				<td>Team 1 member</td>
				<td>T1</td>
				<td>T1</td>
			</tr>
			<tr>
				<td>U2</td>
				<td>Team 2 member</td>
				<td>T2</td>
				<td>T2</td>
			</tr>
			<tr>
				<td>U3</td>
				<td>Team 3 member</td>
				<td>T3</td>
				<td>T3</td>
			</tr>
			<tr>
				<td>U1-2</td>
				<td>Member of teams 1 and 2</td>
				<td>T1, T2</td>
				<td>T1, T2</td>
			</tr>
			<tr>
				<td>U1-3</td>
				<td>Member of teams 1 and 3</td>
				<td>T1, T3</td>
				<td>T1, T3</td>
			</tr>
			<tr>
				<td>U1-2-3</td>
				<td>Member of teams 1,2, and 3</td>
				<td>T1,T2, T3</td>
				<td>T1, T2, T3</td>
			</tr>
		</tbody>
	</table>
</section>
<section>
	<h2 id="Hybrid_Example">Hybrid Example</h2>
	<p>In a real-world situation, you might have a combination of these two arrangements. Say for example we have Managers and Staff. Staff can only see Staff documents and Managers can see Manager and Staff documents. Both types of users can be assigned to teams as well, in which case they can see all of the documents for that team. In addition, say that Managers can access some, but not all, team documents. Staff can only access team documents if they are members of that team.</p>
	<p>In this example, we could set up the following Access Levels:</p>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th scope="col">Access Level</th>
				<th scope="col">Description</th>
				<th scope="col">Roles</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Manager</td>
				<td>Non-team manager documents</td>
				<td>Manager</td>
			</tr>
			<tr>
				<td>Staff</td>
				<td>Non-team staff documents</td>
				<td>Manager, Staff</td>
			</tr>
			<tr>
				<td>Team1</td>
				<td>Sensitive Team1 documents (no access outside team)</td>
				<td>Team1</td>
			</tr>
			<tr>
				<td>Team1-Manager</td>
				<td>Team1 documents that can be accessed by all managers</td>
				<td>Team1, Manager</td>
			</tr>
			<tr>
				<td>Team2</td>
				<td>Sensitive Team2 documents (no access outside team)</td>
				<td>Team2</td>
			</tr>
			<tr>
				<td>Team2-Manager</td>
				<td>Team2 documents that can be accessed by all managers</td>
				<td>Team2, Manager</td>
			</tr>
		</tbody>
	</table>
	<p>Then, users could be assigned to roles as follows:</p>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th scope="col">User Type</th>
				<th scope="col">Role</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Manager on no teams</td>
				<td>Manager</td>
			</tr>
			<tr>
				<td>Staff on no teams</td>
				<td>Staff</td>
			</tr>
			<tr>
				<td>Manager on team 1</td>
				<td>Manager, Team1</td>
			</tr>
			<tr>
				<td>Staff on team 1</td>
				<td>Staff, Team1</td>
			</tr>
			<tr>
				<td>Manager on teams 1 and 2</td>
				<td>Manager, Team1, Team2</td>
			</tr>
			<tr>
				<td>Staff on teams 1 and 2</td>
				<td>Staff, Team1, Team2</td>
			</tr>
		</tbody>
	</table>
</section>