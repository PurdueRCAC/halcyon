<style>
/*#admin {
				background: #000;
				color: #fff;
				margin: 0;
				padding: 0.25em 0.5em;
			}
			#admin a,
			#admin a:link {
				color: #aaa;
			}
			#admin a:hover,
			#admin a:active {
				color: #fff;
			}*/
			#toolbar-administration {
				margin: 0;
				padding: 0;
				vertical-align: baseline;
				font-size: small;
				line-height: 1;
			}
			@media print {
				#toolbar-administration {
					display: none;
				}
			}
			.toolbar-loading #toolbar-administration {
				overflow: hidden;
			}
			.toolbar li,
			.toolbar .item-list,
			.toolbar .item-list li,
			.toolbar .menu-item,
			.toolbar .menu-item--expanded {
				list-style-type: none;
				list-style-image: none;
			}
			.toolbar .menu-item {
				padding-top: 0;
			}
			/*.toolbar .toolbar-bar .toolbar-tab,
			.toolbar .menu-item {
				display: block;
			}
			.toolbar .toolbar-bar .toolbar-tab.hidden {
				display: none;
			}*/
			.toolbar a {
				display: block;
				line-height: 1;
			}
/*
.toolbar .toolbar-bar, .toolbar .toolbar-tray {
	position: relative;
	z-index: 1250;
}
.toolbar-horizontal .toolbar-tray {
	position: fixed;
	left: 0;
	width: 100%;
}
.toolbar-oriented .toolbar-bar {
	position: absolute;
	top: 0;
	right: 0;
	left: 0;
}

.toolbar-oriented .toolbar-tray {
	position: absolute;
	right: 0;
	left: 0;
}

@media (min-width: 61em) {
	.toolbar-loading.toolbar-horizontal .toolbar .toolbar-bar .toolbar-tab:last-child .toolbar-tray {
		position: relative;
		z-index: -999;
		display: block;
		visibility: hidden;
		width: 1px;
	}

	.toolbar-loading.toolbar-horizontal .toolbar .toolbar-bar .toolbar-tab:last-child .toolbar-tray .toolbar-lining {
		width: 999em;
	}

	.toolbar-loading.toolbar-horizontal .toolbar .toolbar-bar .home-toolbar-tab + .toolbar-tab .toolbar-tray {
		display: block;
	}
}

.toolbar-oriented .toolbar-bar {
	z-index: 502;
}*/

/*body.toolbar-fixed .toolbar-oriented .toolbar-bar {
	position: fixed;
}*/

.toolbar-loading.toolbar-horizontal .toolbar .toolbar-tray .toolbar-menu > li,
.toolbar .toolbar-bar .toolbar-tab,
.toolbar .toolbar-tray-horizontal li {
	float: left;
}

[dir="rtl"] .toolbar-loading.toolbar-horizontal .toolbar .toolbar-tray .toolbar-menu > li,
[dir="rtl"] .toolbar .toolbar-bar .toolbar-tab,
[dir="rtl"] .toolbar .toolbar-tray-horizontal li {
	float: right;
}

@media only screen {
	.toolbar .toolbar-bar .toolbar-tab,
	.toolbar .toolbar-tray-horizontal li {
		float: none;
	}

	[dir="rtl"] .toolbar .toolbar-bar .toolbar-tab,
	[dir="rtl"] .toolbar .toolbar-tray-horizontal li {
		float: none;
	}
}

@media (min-width: 16.5em) {
	.toolbar .toolbar-bar .toolbar-tab,
	.toolbar .toolbar-tray-horizontal li {
		float: left;
	}
	[dir="rtl"] .toolbar .toolbar-bar .toolbar-tab,
	[dir="rtl"] .toolbar .toolbar-tray-horizontal li {
		float: right;
	}
}

.toolbar-oriented .toolbar-bar .toolbar-tab,
.toolbar-oriented .toolbar-tray-horizontal li {
	float: left;
}
[dir="rtl"] .toolbar-oriented .toolbar-bar .toolbar-tab,
[dir="rtl"] .toolbar-oriented .toolbar-tray-horizontal li {
	float: right;
}
/*
.toolbar .toolbar-tray {
	z-index: 501;
	display: none;
}


.toolbar .toolbar-bar .toolbar-tab > .toolbar-icon {
	position: relative;
	z-index: 502;
}

.toolbar-oriented .toolbar-tray-horizontal .menu-item ul {
	display: none;
}

.toolbar .toolbar-tray-vertical.is-active,
body.toolbar-fixed .toolbar .toolbar-tray-vertical {
	position: fixed;
	overflow-x: hidden;
	overflow-y: auto;
	height: 100%;
}*/
/*
.toolbar .toolbar-tray.is-active {
	display: block;
}

.toolbar .toolbar-tray .toolbar-toggle-orientation {
	display: none;
}

.toolbar-oriented .toolbar-tray .toolbar-toggle-orientation {
	display: block;
}

.toolbar-oriented .toolbar-tray-horizontal .toolbar-toggle-orientation {
	position: absolute;
	top: auto;
	right: 0;
	bottom: 0;
}

[dir="rtl"] .toolbar-oriented .toolbar-tray-horizontal .toolbar-toggle-orientation {
	right: auto;
	left: 0;
}

.toolbar .toolbar-bar .home-toolbar-tab {
	display: none;
}

.path-admin .toolbar-bar .home-toolbar-tab {
	display: block;
}*/
.toolbar {
	font-family: "Source Sans Pro", "Lucida Grande", Verdana, sans-serif;
	font-size: 0.8125rem;
	-moz-tap-highlight-color: rgba(0, 0, 0, 0);
	-o-tap-highlight-color: rgba(0, 0, 0, 0);
	-webkit-tap-highlight-color: rgba(0, 0, 0, 0);
	tap-highlight-color: rgba(0, 0, 0, 0);
	-moz-touch-callout: none;
	-o-touch-callout: none;
	-webkit-touch-callout: none;
	touch-callout: none;
}

.toolbar .toolbar-item {
	padding: 1em 1.3333em;
	cursor: pointer;
	text-decoration: none;
	line-height: 1em;
}

.toolbar .toolbar-item:hover, .toolbar .toolbar-item:focus {
	text-decoration: underline;
}

.toolbar .toolbar-bar {
	color: #ddd;
	background-color: #0f0f0f;
	box-shadow: -1px 0 3px 1px rgba(0, 0, 0, 0.3333);
}

[dir="rtl"] .toolbar .toolbar-bar {
	box-shadow: 1px 0 3px 1px rgba(0, 0, 0, 0.3333);
}

.toolbar .toolbar-bar .toolbar-item {
	color: #fff;
}

.toolbar .toolbar-bar .toolbar-tab > .toolbar-item {
	font-weight: bold;
}

.toolbar .toolbar-bar .toolbar-tab > .toolbar-item:hover, .toolbar .toolbar-bar .toolbar-tab > .toolbar-item:focus {
	background-image: -webkit-linear-gradient(rgba(255, 255, 255, 0.125) 20%, transparent 200%);
	background-image: linear-gradient(rgba(255, 255, 255, 0.125) 20%, transparent 200%);
}

.toolbar .toolbar-bar .toolbar-tab > .toolbar-item.is-active {
	background-image: -webkit-linear-gradient(rgba(255, 255, 255, 0.25) 20%, transparent 200%);
	background-image: linear-gradient(rgba(255, 255, 255, 0.25) 20%, transparent 200%);
}

.toolbar .toolbar-tray {
	background-color: #000;
}

.toolbar-horizontal .toolbar-tray > .toolbar-lining {
	padding-right: 5em;
}

[dir="rtl"] .toolbar-horizontal .toolbar-tray > .toolbar-lining {
	padding-right: 0;
	padding-left: 5em;
}
.toolbar-horizontal .toolbar-tray {
	border-bottom: 1px solid #aaa;
	box-shadow: -2px 1px 3px 1px rgba(0, 0, 0, 0.3333);
}

[dir="rtl"] .toolbar-horizontal .toolbar-tray {
	box-shadow: 2px 1px 3px 1px rgba(0, 0, 0, 0.3333);
}

.toolbar .toolbar-tray-horizontal .toolbar-tray {
	background-color: #f5f5f5;
}

.toolbar-tray a {
	padding: 1em 1.3333em;
	cursor: pointer;
	text-decoration: none;
	color: #aaa;
}

.toolbar-tray a:hover,
.toolbar-tray a:active,
.toolbar-tray a:focus,
.toolbar-tray a.is-active {
	text-decoration: underline;
	color: #fff;
}

.toolbar .toolbar-menu {
	background-color: #fff;
	margin: 0;
}

.toolbar-tray .menu-item + .menu-item {
	border-left: 1px solid #333;
}
[dir="rtl"] .toolbar-tray .menu-item + .menu-item {
	border-right: 1px solid #333;
	border-left: 0 none;
}
.toolbar-tray .menu-item:last-child {
	border-right: 1px solid #333;
}
[dir="rtl"] .toolbar-horizontal .toolbar-tray .menu-item:last-child {
	border-left: 1px solid #ddd;
}

.toolbar .toolbar-menu .toolbar-menu a {
	color: #434343;
}
/*
.toolbar .toolbar-toggle-orientation {
	height: 100%;
	padding: 0;
	background-color: #f5f5f5;
}
.toolbar-tray .toolbar-toggle-orientation {
	border-left: 1px solid #c9c9c9;
}

[dir="rtl"] .toolbar-tray .toolbar-toggle-orientation {
	border-right: 1px solid #c9c9c9;
	border-left: 0 none;
}

.toolbar .toolbar-toggle-orientation > .toolbar-lining {
	float: right;
}

[dir="rtl"] .toolbar .toolbar-toggle-orientation > .toolbar-lining {
	float: left;
}

.toolbar .toolbar-toggle-orientation button {
	display: inline-block;
	cursor: pointer;
}
*/
.toolbar .toolbar-icon {
	position: relative;
	padding-left: 2.25em;
}

[dir="rtl"] .toolbar .toolbar-icon {
	padding-right: 2.75em;
	padding-left: 1.3333em;
}

.toolbar .toolbar-icon:before {
	position: absolute;
	top: 0;
	left: 0.6667em;
	display: block;
	width: 1.2em;
	height: 100%;
	content: "";
	background-color: transparent;
	background-repeat: no-repeat;
	background-attachment: scroll;
	background-position: center center;
	background-size: 100% auto;
}

[dir="rtl"] .toolbar .toolbar-icon:before {
	right: 0.6667em;
	left: auto;
}

.toolbar button.toolbar-icon {
	border: 0;
	background-color: transparent;
	font-size: 1em;
}

.toolbar .toolbar-menu ul .toolbar-icon {
	padding-left: 1.3333em;
}

[dir="rtl"] .toolbar .toolbar-menu ul .toolbar-icon {
	padding-right: 1.3333em;
	padding-left: 0;
}

.toolbar .toolbar-menu ul a.toolbar-icon:before {
	display: none;
}

.toolbar-bar .toolbar-icon-menu:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/bebebe/hamburger.svg);
}

.toolbar-bar .toolbar-icon-menu:active:before, .toolbar-bar .toolbar-icon-menu.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/ffffff/hamburger.svg);
}

.toolbar-bar .toolbar-icon-help:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/bebebe/questionmark-disc.svg);
}

.toolbar-bar .toolbar-icon-help:active:before, .toolbar-bar .toolbar-icon-help.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/ffffff/questionmark-disc.svg);
}

.toolbar-icon-system-admin-content:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/file.svg);
}

.toolbar-icon-system-admin-content:active:before, .toolbar-icon-system-admin-content.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/000000/file.svg);
}

.toolbar-icon-system-admin-structure:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/orgchart.svg);
}

.toolbar-icon-system-admin-structure:active:before,
.toolbar-icon-system-admin-structure.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/000000/orgchart.svg);
}

.toolbar-icon-system-themes-page:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/paintbrush.svg);
}

.toolbar-icon-system-themes-page:active:before,
.toolbar-icon-system-themes-page.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/000000/paintbrush.svg);
}

.toolbar-icon-entity-user-collection:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/people.svg);
}

.toolbar-icon-entity-user-collection:active:before, .toolbar-icon-entity-user-collection.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/000000/people.svg);
}

.toolbar-icon-system-modules-list:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/puzzlepiece.svg);
}

.toolbar-icon-system-modules-list:active:before, .toolbar-icon-system-modules-list.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/000000/puzzlepiece.svg);
}

.toolbar-icon-system-admin-config:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/wrench.svg);
}

.toolbar-icon-system-admin-config:active:before, .toolbar-icon-system-admin-config.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/000000/wrench.svg);
}

.toolbar-icon-system-admin-reports:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/barchart.svg);
}

.toolbar-icon-system-admin-reports:active:before, .toolbar-icon-system-admin-reports.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/000000/barchart.svg);
}

.toolbar-icon-help-main:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/questionmark-disc.svg);
}

.toolbar-icon-help-main:active:before, .toolbar-icon-help-main.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/000000/questionmark-disc.svg);
}

@media only screen and (min-width: 16.5em) {
	.toolbar .toolbar-bar .toolbar-tab > .toolbar-icon {
		width: 4em;
		margin-right: 0;
		margin-left: 0;
		padding-right: 0;
		padding-left: 0;
		text-indent: -9999px;
	}

	.toolbar .toolbar-bar .toolbar-tab > .toolbar-icon:before {
		left: 0;
		width: 100%;
		background-size: 42% auto;
	}

	.no-svg .toolbar .toolbar-bar .toolbar-tab > .toolbar-icon:before {
		background-size: auto auto;
	}

	[dir="rtl"] .toolbar .toolbar-bar .toolbar-tab > .toolbar-icon:before {
		right: 0;
		left: auto;
	}
}

@media only screen and (min-width: 36em) {
	.toolbar .toolbar-bar .toolbar-tab > .toolbar-icon {
		width: auto;
		padding-right: 1.3333em;
		padding-left: 2.75em;
		text-indent: 0;
		background-position: left center;
	}

	[dir="rtl"] .toolbar .toolbar-bar .toolbar-tab > .toolbar-icon {
		padding-right: 2.75em;
		padding-left: 1.3333em;
		background-position: right center;
	}

	.toolbar .toolbar-bar .toolbar-tab > .toolbar-icon:before {
		left: 0.6667em;
		width: 20px;
		background-size: 100% auto;
	}

	.no-svg .toolbar .toolbar-bar .toolbar-tab > .toolbar-icon:before {
		background-size: auto auto;
	}

	[dir="rtl"] .toolbar .toolbar-bar .toolbar-tab > .toolbar-icon:before {
		right: 0.6667em;
		left: 0;
	}
}

.toolbar-tab a:focus {
	text-decoration: underline;
	outline: none;
}

.toolbar-lining button:focus {
	outline: none;
}

.toolbar-tray-horizontal a:focus, .toolbar-box a:focus {
	outline: none;
	background-color: #f5f5f5;
}

.toolbar-box a:hover:focus {
	text-decoration: underline;
}

.toolbar .toolbar-icon.toolbar-handle:focus {
	outline: none;
	background-color: #f5f5f5;
}

.toolbar .toolbar-icon.toolbar-handle {
	width: 4em;
	text-indent: -9999px;
}

.toolbar .toolbar-icon.toolbar-handle:before {
	left: 1.6667em;
}

[dir="rtl"] .toolbar .toolbar-icon.toolbar-handle:before {
	right: 1.6667em;
	left: auto;
}

.toolbar .toolbar-icon.toolbar-handle:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/5181c6/chevron-disc-down.svg);
}

.toolbar .toolbar-icon.toolbar-handle.open:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/chevron-disc-up.svg);
}

.toolbar .toolbar-menu .toolbar-menu .toolbar-icon.toolbar-handle:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/5181c6/twistie-down.svg);
	background-size: 75%;
}

.toolbar .toolbar-menu .toolbar-menu .toolbar-icon.toolbar-handle.open:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/twistie-up.svg);
	background-size: 75%;
}

.toolbar .toolbar-icon-escape-admin:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/bebebe/chevron-disc-left.svg);
}

[dir="rtl"] .toolbar .toolbar-icon-escape-admin:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/bebebe/chevron-disc-right.svg);
}

.toolbar .toolbar-toggle-orientation button {
	width: 39px;
	height: 39px;
	padding: 0;
	text-indent: -999em;
}

.toolbar .toolbar-toggle-orientation button:before {
	right: 0;
	left: 0;
	margin: 0 auto;
}

[dir="rtl"] .toolbar .toolbar-toggle-orientation .toolbar-icon {
	padding: 0;
}

.toolbar .toolbar-toggle-orientation [value="vertical"]:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/bebebe/push-left.svg);
}

.toolbar .toolbar-toggle-orientation [value="vertical"]:hover:before, .toolbar .toolbar-toggle-orientation [value="vertical"]:focus:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/push-left.svg);
}

[dir="rtl"] .toolbar .toolbar-toggle-orientation [value="vertical"]:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/bebebe/push-right.svg);
}

[dir="rtl"] .toolbar .toolbar-toggle-orientation [value="vertical"]:hover:before, [dir="rtl"] .toolbar .toolbar-toggle-orientation [value="vertical"]:focus:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/push-right.svg);
}

.toolbar .toolbar-toggle-orientation [value="horizontal"]:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/bebebe/push-up.svg);
}

.toolbar .toolbar-toggle-orientation [value="horizontal"]:hover:before, .toolbar .toolbar-toggle-orientation [value="horizontal"]:focus:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/787878/push-up.svg);
}

.toolbar-bar .toolbar-icon-user:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/bebebe/person.svg);
}

.toolbar-bar .toolbar-icon-user:active:before, .toolbar-bar .toolbar-icon-user.is-active:before {
	background-image: url(/drupal/core/themes/stable/images/core/icons/ffffff/person.svg);
}
.toolbar .icn {
	width: 0.8em;
	position: absolute;
	top: 0.6em;
	left: 0.5em;
	display: block;
}
.toolbar .icn svg {
	width: 100%;
	stroke: #aaa;
}
</style>
<div id="toolbar-administration" class="toolbar toolbar-oriented">
	<div id="toolbar-item-administration-tray" data-toolbar-tray="toolbar-item-administration-tray" class="toolbar-tray is-active toolbar-tray-horizontal">
		<nav class="toolbar-lining clearfix" role="navigation" aria-label="Administration menu">
			<h1 class="toolbar-tray-name sr-only">Administration menu</h1>
			<div class="toolbar-menu-administration">
				<ul class="toolbar-menu">
					<li class="menu-item menu-item--collapsed">
						<a href="{{ route('admin.config') }}" title="Administer settings" id="toolbar-link-system-admin_config" class="toolbar-icon"><span class="icn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" fill="none" stroke-miterlimit="10" stroke-width="2"/></svg></span> Configuration</a>
					</li>
					<li class="menu-item">
						<a href="{{ route('admin.users.index') }}" title="Manage user accounts, roles, and permissions." id="toolbar-link-entity-user-collection" class="toolbar-icon"><span class="icn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><circle cx="9" cy="7" r="4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M23 21v-2a4 4 0 0 0-3-3.87" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M16 3.13a4 4 0 0 1 0 7.75" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></span> Users</a>
					</li>
					<li class="menu-item menu-item--collapsed">
						<a href="{{ route('admin.menus.index') }}" title="Administer blocks, content types, menus, etc." id="toolbar-link-system-admin_structure" class="toolbar-icon"><span class="icn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"/><line x1="8" y1="12" x2="21" y2="12" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"/><line x1="8" y1="18" x2="21" y2="18" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"/><line x1="3" y1="6" x2="3" y2="6" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"/><line x1="3" y1="12" x2="3" y2="12" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"/><line x1="3" y1="18" x2="3" y2="18" fill="none" stroke-linecap="round" stroke-miterlimit="10" stroke-width="2"/></svg></span> Menus</a>
					</li>
					<li class="menu-item menu-item--collapsed">
						<a href="{{ route('admin.pages.index') }}" title="Find and manage content." id="toolbar-link-system-admin_content" class="toolbar-icon"><span class="icn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg></span> Content</a>
					</li>
					<li class="menu-item">
						<a href="{{ route('admin.themes.index') }}" title="Select and configure themes." id="toolbar-link-system-themes_page" class="toolbar-icon"><span class="icn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg></span> Themes</a>
					</li>
					<li class="menu-item">
						<a href="{{ route('admin.widgets.index') }}" title="Add and enable modules to extend site functionality." id="toolbar-link-system-modules_list" class="toolbar-icon"><span class="icn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12.89 1.45l8 4A2 2 0 0 1 22 7.24v9.53a2 2 0 0 1-1.11 1.79l-8 4a2 2 0 0 1-1.79 0l-8-4a2 2 0 0 1-1.1-1.8V7.24a2 2 0 0 1 1.11-1.79l8-4a2 2 0 0 1 1.78 0z" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><polyline points="2.32 6.16 12 11 21.68 6.16" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><line x1="12" y1="22.76" x2="12" y2="11" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><line x1="7" y1="3.5" x2="17" y2="8.5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></span> Extensions</a>
					</li>
					<li class="menu-item">
						<a href="/drupal/admin/help" title="Reference for usage, configuration, and modules." id="toolbar-link-help-main" class="toolbar-icon"><span class="icn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9.09,9a3,3,0,0,1,5.83,1c0,2-3,3-3,3" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><circle cx="12" cy="12" r="10" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><line x1="12" y1="17" x2="12" y2="17" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></span> Help</a>
					</li>
				</ul>
			</div>
		</nav>
	</div>
</div>
