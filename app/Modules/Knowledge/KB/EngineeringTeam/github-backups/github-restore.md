---
title: Restoring GitHub Enterprise from backups
tags:
 - internal
---

# Restoring GitHub Enterprise From Backups
To restore from the last successful backup
1. SSH to ``github-backups.rcac.purdue.edu``
2. Run ``sudo su rcacghe -c '/opt/github-backup-utils/bin/ghe-restore github.rcac.purdue.edu'``

For more information, refer to
<https://github.com/github/backup-utils#using-the-backup-and-restore-commands>
