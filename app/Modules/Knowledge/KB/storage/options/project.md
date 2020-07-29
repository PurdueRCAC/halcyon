---
title: Project Directory
tags:
 - linuxclusteritar
---

# Project Directory
ITaP provides <em>project directories</em> for storing important program files, scripts, input data sets, critical results, and frequently used files that should be accessible to an entire research group.  These files are shared on ${resource.name} Data Depot, which is a high-capacity, fast, reliable and secure data storage service designed, configured, and operated for restricted data.  These data files are only accessible via ${resource.name}.

## ${resource.name} Data Depot Features

<p>${resource.name} Data Depot offers research groups in need of restricted data storage unique features and benefits:</p>
<ul class = "feature_list">
	<li>
		Available
		<p>
			Research groups that use Weber have access to unlimited mirrored data storage without additional charges.
		</p>
	</li>
	<li>
		Accessible
		<p>
				Directly from ${resource.name}.
		</p>
	</li>
	<li>
		Capable
		<p>
			The ${resource.name} Data Depot facilitates joint work on shared files across your research group, avoiding the need for numerous copies of datasets across individuals' home or scratch directories. It is an ideal place to store group applications, tools, scripts, and documents.
		<p>
	</li>
	<li>
		Controllable Access
		<p>
			Access is managed in consultation with the Export Control Office. Additional Unix groups may be created to assist you in setting appropriate permissions to allow exactly the access you want and prevent any you do not.
		</p>
	</li>
	<li>
		Data Retention
		<p>
			All data kept in the ${resource.name} Data Depot remains owned by the research group's lead faculty.  When researchers or students leave your group, any files left in their home directories may become difficult to recover.  Files kept in ${resource.name} Data Depot remain with the research group, unaffected by turnover, and could head off potentially difficult disputes.
		</p>
	</li>
	<li>
		Never Purged
		<p>
			The ${resource.name} Data Depot is never subject to purging.
		</p>
	</li>
	<li>
		Reliable
		<p>
			The ${resource.name} Data Depot is redundant and protected against hardware failures and accidental deletion.</br>
ITaP maintains daily snapshots of your project directory for seven days in the event of accidental deletion. Cold storage backups of snapshots are kept for 90 days.  For additional security, you should store another copy of your files on more permanent storage, such as the [Fortress HPSS Archive](/storage/fortress/).

		</p>
	</li>
	<li>
		Restricted Data
		<p>
			 The ${resource.name} Data Depot is approved for ITAR/CUI restricted data.
		</p>
	</li>
</ul>

## ${resource.name} Data Depot Hardware Details

The ${resource.name} Data Depot uses an enterprise-class ZFS storage solution with an initial total capacity of 10 TB.  This storage is redundant, reliable, and features regular snapshots.  The ${resource.name} Data Depot is non-purged space suitable for tasks such as sharing data, editing files, developing and building software, and many other uses.  Built on Data Direct Networks' SFA12k storage platform, the ${resource.name} Data Depot has redundant storage arrays.


## Default Configuration

This is what a default configuration looks like for a research group called "mylab":
<pre>
/depot/mylab/
            +--apps/
            |
            +--data/
            |
            +--etc/
            |     +--bashrc
            |     +--cshrc
            |
 (other subdirectories)
</pre> 

 The <kbd>/depot/mylab/</kbd> directory is the main top-level directory for all your research group storage.  All files are to be kept within one of the subdirectories of this, based on your specific access requirements.  ITaP will create these subdirectories after consulting with you as to exactly what you need.


 By default, ITaP will create the following subdirectories, with the following access and use models.  All of these details can be changed to suit the particular needs of your research group.

<ul>
 <li>
 <kbd>data/</kbd><br />
 Intended for read and write use by a limited set of people chosen by the research group's managers.<br />
 Restricted to not be readable or writable by anyone else.<br />
 <em>This is frequently used as an open space for storage of shared research data.</em>
 </li>
 <li>
 <kbd>apps/</kbd><br />
 Intended for write use by a limited set of people chosen by the research group's managers.<br />
 Restricted to not be writable by anyone else.<br />
 Allows read and execute by anyone who has access to any cluster queues owned by the research group and anyone who has other file permissions granted by the research group (such as "data" access above).<br />
 <em>This is frequently used as a space for central management of shared research applications.</em>
 </li>
 <li>
 <kbd>etc/</kbd><br />
 Intended for write use by a limited set of people chosen by the research group's managers (by default, the same as for "apps" above).<br />
 Restricted to not be writable by anyone else.<br />
 Allows read and execute by anyone who has access to any cluster queues owned by the research group and anyone who has other file permissions granted by the research group (such as "data" access above).<br />
 <em>This is frequently used as a space for central management of shared startup/login scripts, environment settings, aliases, etc.</em>
 </li>
 <li>
 <kbd>etc/bashrc</kbd><br />
 <kbd>etc/cshrc</kbd><br />
 Examples of group-wide shell startup files.  Group members can source these from their own <kbd>$HOME/.bashrc</kbd> or <kbd>$HOME/.cshrc</kbd> files so they would automatically pick up changes to their environment needed to work with applications and data for the research group.  There are more detailed instructions in these files on how to use them.
 </li>
 <li>
 Additional subdirectories can be created as needed in the top and/or any of the lower levels.  Just contact <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> and we will be happy to figure out what will work best for your needs.
 </li>
</ul>
