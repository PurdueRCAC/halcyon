---
title: Default Configuration
tags:
 - depot
---

# Default Configuration

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
