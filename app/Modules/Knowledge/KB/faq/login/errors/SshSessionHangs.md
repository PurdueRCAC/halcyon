---
title: "My SSH connection hangs"
tags:
 - faq
 - internal
---

### Problem

Your console hangs while trying to connect to a RCAC Server.

### Solution

This can happen due to various reasons. Most common reasons for hanging SSH terminals are:

* **Network:** If you are connected over wifi, make sure that your Internet connection is fine.
* **Busy front-end server:** When you connect to a cluster, you SSH to one of the front-ends. Due to transient user loads, one or more of the front-ends may become unresponsive for a short while. To avoid this, try reconnecting to the cluster or wait until the server you have connected to has reduced load.
* **File system issue:** If a server has issues with one or more of the file systems (`home`, `scratch`, or `depot`) it may freeze your terminal. To avoid this you can connect to another front-end.

If neither of the suggestions above work, please contact rcac-help@purdue.edu specifying the name of the server where your console is hung.

{::if user.staff == 1}
### Staff Notes

Troubleshooting may involve multiple steps depending on complexity of the situation.

* Figure out which is the offending front-end. Alert systems team to fix it or remove it from DNS.
* If this is a cluster-wide filesystem issue (e.g., `depot`), check whether the user has `module load` in her profile file (`~/.bashrc` or `~/.cshrc`) or any other references to that file system. Ask user to edit the profile file temporarily to avoid those references.

{::/}
