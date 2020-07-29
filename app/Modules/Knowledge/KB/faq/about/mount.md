---
title: What is the best way to mount ${resource.name} in my lab?
tags:
 - depot
---

### What is the best way to mount ${resource.name} in my lab?

You can mount your ${resource.name} space via [Network Drives / CIFS](../../../storage/transfer/cifs) using your Purdue Career Account. NFS access may also be possible depending on your lab's environment. If you require NFS access, contact <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a> to to discuss.

{::if user.staff == 1}
### Staff Notes

CIFS and SSHfs are fairly straightforward. You simply need to point them at either datadepot.rcac or an appropriate frontend.

NFS however requires changes to our configuration. Under puppet, the files are located at modules/nfs/files/gpfs-c###

A good example of an exports line:
/depot/chunh            matrix1.stat.purdue.edu(rw,secure,root_squash,fsid=123)

This specifies the specific group space to export (never export /depot) to a named machine (matrix1.stat.purdue.edu) and specifies that the mount request must come from a secure port (<1024) and that all requests by UID 0 on matrix1 be squashed. Please note that you *must* increment fsid if /depot/chunh is exported anywhere else in the file!

Such an export like the above should only go to a machine that shares a coordinated UID space with us through acmaint!  If the lab or machine in a request does not, you need to squash all UID requests:

/depot/example		random.dept.purdue.edu(rw,secure,all_squash,anonuid=example-uid,anongid=example-gid,fsid=134)

That export will ensure all operations from the client take place as UID/GID specified by anonuid and anongid. If this is needed, you should request a service account for the group and make that account's UID/GID these values.
{::/}
