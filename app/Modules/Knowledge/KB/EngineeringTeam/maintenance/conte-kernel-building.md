---
title: Kernel Compilation
tags:
 - internal
---

This documentation is for compiling the kernel on Conte and other clusters, as
told by Scott Hicks.

# Conte
Most recent Conte kernel build (prior to 2017-07-06):
* Scott updated a test node with a brand new repo
* Amiya patched and built the MPSS kernel
* Amiya built OFED against the new kernel and it worked easily
* Build vTune and Torque

# Other Clusters
1. Mellanox OFED suites has a script for getting kernel support beyond the basic
   RHEL 6.9 point release kernel (process is very painless and cookie cutter)
2. Install the new kernel and reboot
3. Then build Lustre against the running (new) kernel
4. Install Lustre packgaes in ``/usr/rmt_share/packages/{lustre,ofed}``, etc
5. Copy ``/usr/rmt_share/packages/lustre/rpmbuild`` to the system running the new
   kernel and then run ``rpmbuild --rebuild``
6. Then move the new RPMs back to ``/usr/rmt_share/packages/lustre``

Lustre is usually only a problem when using a very new Lustre or OFED---
sometimes there are problems with conflicting or missing ``#includes`` in the
kernel headers which break the build.

# Previous Problems
* Sometimes would have to compile OFED, then Lustre, and then recompile OFED
  again (to bring in some Lustre things)
* Backporting the dirty cow patch to an older kernel because newer kernels
  would not compile with MPSS---hopefully should not be an issue anymore.
* Custom kernels located in ``/usr/rmt_share/admin/root/custom_kernels``
