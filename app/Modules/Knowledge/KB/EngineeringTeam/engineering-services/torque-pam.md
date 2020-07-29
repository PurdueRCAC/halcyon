---
title: Torque PAM Integration - What's Complete Thus Far
tags:
 - internal
---

# Update 2017-09-05
The `pam_pbssimpleauth.so` file is provided by the `torque-pam` package, but our
version of Torque was not compiled with the flag `--with-pam` so the module
currently doesn't do anything until we replace our Torque installation.

--Jason

# Torque PAM Integration - What's Complete So Far
Our goal for this project is to prevent users from logging into a compute node
unless they have a job running on that node. To accomplish this, PAM needs to
perform authentication tasks using Torque's PAM module. To have a clear
distinction of what is required, here are the list of requirements:

* Only impact compute nodes
* Any given node prevents users who do not have jobs from logging in
* Any given node allows users who have a job running on said node to log in
* Any given node always allows system administrators to log in

Torque does contain an option to build the resource manager with PAM modules
to allow a configuration preventing users without running jobs from logging
into a node. Adaptive Computing contains
[a tutorial](http://docs.adaptivecomputing.com/torque/3-0-5/3.4hostsecurity.php)
on their website about how to configure this policy. In short the necessary
configuration requires adding `auth  required  pam_pbssimpleauth.so` to the
PAM.D configuration file for the service that will limit users from logging in.
Most likely this PAM.D configuration file will be the SSHD file at
`/etc/pam.d/sshd`.

The dependency to this Torque PAM.D configuration is a shared library compiled
with Torque called `pam_pbssimpleauth.so` that is then placed in
`/lib64/security`. Unfortunately the `pam_pbssimpleauth.so`
shared library is not compiled with Torque by default and is not located within
the production RPMs delivered from Adaptive Computing (it is provided in the
`torque-pam` package on CentOS). The
`pam_pbssimpleauth.so` must be compiled manually (it never hurts to double
check that the PAM.D library or RPM is not available from Adaptive). To compile
Torque, download the source, configure it with the flag `--with-pam`, then
compile the program. Once compiled the Torque PAM.D shared library 
`pam_pbssimpleauth.so` is located at
`<torque-dir>/src/pam/.libs/pam_pbssimpleauth`. A copy for Torque 5.1.3 is
currently compiled at 
`/depot/itap/cook71/rhel/src/torque-5.1.3-1462984387_205d70d/src/pam/.libs/pam_pbssimpleauth.so`.

During my testing I found that simply following the tutorial I linked to above
did not result in predicted behavior. Unfortunately I did not have enough time
to spare for testing every situation, the next step I intended to follow was
specifying the `pam_pbssimpleauth.so` library as an absolute path in the PAM.D
configuration file. The library did not appear to be automatically discovered
by the PAM.D service, otherwise it may be that the version of Torque that was
compiled with the flag `--with-pam` must be installed along side the
`pam_pbssimpleauth.so` shared library. I tested this compiled PAM.D module on
Halstead which we delivered with Torque packages build by Adaptive, where the
majority of our clusters are running a version of Torque that was compiled in
house. I do not believe this should impact the `pam_pbssimpleauth.so`
functionality as it appears the library relies on the Torque libraries and not
add functions to the core library. The `pam_pbssimpleauth.so` should be a
standalone shared library that does not need any unique additions to the core
Torque suite. This may be incorrect, but my hopes reside with this possibility
as it means we can add the Torque PAM.D functionality without having to
recompile Torque, allowing us to use Adaptive's Torque packages and reducing
our workload in the long run.

As a starting point for learning how to configure PAM.D, I urge you to visit
the [CentOS documentation on PAM](https://www.centos.org/docs/5/html/Deployment_Guide-en-US/ch-pam.html)
as it was a valuable place for me to begin. It will also behoove you to
learn about other standard PAM libraries to assist in fully configuring PAM.D
to the needs of RCAC. One such PAM library is `pam_group.so` which permits
defining a list of users as those who are able to log in. I was working on the
following configuration for the `account` statements in `/etc/pam.d/sshd` 
before I had to stop:

```
account  required   pam_nologin.so
account  include    password-auth   # A standard authentication we check against
account  sufficient pam_pbssimpleauth.so
account  required   pam_group.so
```

The `pam_group.so` should be removed and `pam_pbssimpleauth.so` should be set
to `required` during testing stages.

This is all that I have, hopefully it will be enough to put this project on its
way. Good luck.

-Seth Cook
