---
title: Customer NFS Depot Export
tags:
 - internal
---

# Customer NFS Depot Export

Here are the basic steps to export a customer nfs depot directory

1. Add the appropriate depot export to /RCAC-Staff/modules/nfs/files/depot-svc00[0-2] (take care in determing FSID if multiple depot directories are being exported to the same machine)
2. Run puppet on the depot-svc00[0-2] machine (this module currently appears to be broken (does not add the export to the machine), but we should still keep the exports up to date until we get it fixed)
3. Add the same exports (step #1) to /etc/exports on the depot-svc00[0-2] machines
4. Run the command, 'sudo exportfs -a' on each of the depot-svc00[0-2] machines

Notes:
- if user states the exports are available but the contents seem incorrect, verify that different FSIDs are being used for each depot directory which are being exported to the same machine
- if user states the exports are available, but the owners/groups are set to nobody:nobody, verify the machine is using ACMAINT and that NFSv3 is being used to mount the directory from depot 
