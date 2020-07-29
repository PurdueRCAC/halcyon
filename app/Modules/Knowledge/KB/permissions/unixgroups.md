---
title: Storage Access Unix Groups
tags:
 - depot
---

# Storage Access Unix Groups

To enable a wide variety of access permissions, users are assigned one or more auxiliary Unix groups.  It is the combination of this Unix group membership and the r/w/x permission bits on subdirectories that allow fine-tuning who can and can not do what within specific areas of your ${resource.name}.  These Unix groups will generally closely match the name of your ${resource.name} root directory and the name of the subdirectory to which write access is being given.  For example, write access to <kbd>/depot/mylab/data/</kbd> is controlled by membership in the <kbd>mylab-data</kbd> Unix group.

There is also one Unix group which has the name of the base directory of your ${resource.name}, <kbd>mylab</kbd>.  This group serves to limit read/execute access to your base <kbd>/depot/mylab/</kbd> directory and also helps to define the read/execute permissions of some of the subdirectories within.  This Unix group is composed of the union of the following:

<ul>
 <li>
 all members of your more specific Unix groups
 </li>
 <li>
 all users authorized to access any of your research group's cluster queues
 </li>
 <li>
 any other specific individuals you may have approved
 </li>
</ul>
 <p>
 Research group faculty and their designees may directly manage membership in these Unix groups, and by extension, the storage access permissions they grant, through the online web application.  Be sure to click the blue double arrows to the right in the table header.
</p>
<ul>
 <li><a href="/account/user/">Research&nbsp;Computing&nbsp;User&nbsp;Management</a></li>
</ul>

<br>
## Checking Your Group Membership

As a user you can check which groups you are a member of by issuing the ``groups`` command while logged into any Research Computing resource.
You can also look on the website at https://www.rcac.purdue.edu/account/myinfo/.

```sh
$ groups
mylab mylab-apps mylab-data
```

If you have recently been added to a group you need to log out and then back in again before the permissions changes
take effect.
