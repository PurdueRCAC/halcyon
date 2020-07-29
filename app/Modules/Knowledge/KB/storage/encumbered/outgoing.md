---
title: Outgoing Files
tags:
 - reed
---

### Overview

Each project on REED has its own outgoing server, named with the project name in lower case followed by `-out.purduereed.lcl` <br />
For example, if your Project name is `Proj-1`, your outgoing server name will be <br />
`proj-1-out.purduereed.lcl`

Before you can transfer files out of the REED environment, you will need to set up a pair of directories to act as your 'airlock'.  This only needs to be done one time; the directories will remain in place after their first use.

The transfer of files outside REED must be approved by at least two team members including yourself.

### Airlock setup
* Use the PuTTY program within REED to connect to your outgoing server.
* The protocol should be SSH (the default for PuTTY), and you should use your REED ID and password.
* Two directories will be created -- `out` and `out_approved`.
 

### Transfer files

Once your two transfer directories are created, you can use them to move files out of REED.  Please remember that this process is subject to federal regulation and must be followed for any data transfer out of the protected environment.

From within the REED Desktop:

* Collect the set of files to be transferred into a single ZIP archive.
* If any of the files are considered sensitive, encrypt the archive.
* Use WinSCP to connect to your Project's outgoing server (see Overview on this page)
* Put your files into the `out` directory.

There may be errors generated due to the fact you can only write, but not read the `out` directory.  These errors can be ignored.

Once the ZIP archive is in your "out" directory on the outgoing server, a different project member must approve the transfer.  This can be any other member of the same project.


[File Transfer Approval](../approval)
