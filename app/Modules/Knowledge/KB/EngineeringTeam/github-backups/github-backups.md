---
title: GitHub Enterprise Backups
tags:
 - internal
---

# Creating Backups
GitHub Enterprise backups are created every four hours, and they are stored in
``/depot/itap/github-backups``
The incremental backup process takes under 10 minutes.

## Manual Backup
To create a manual backup, ``su`` to ``rcacghe`` and run
``/opt/github-backup-utils/bin/ghe-backup``

# Upgrading "backup-utils"
1. Download the latest release tarball from <https://github.com/github/backup-utils/releases>
   and extract it
2. Copy the file ``/opt/github-backup-utils/backup.config`` from
   ``github-backups.rcac`` to the extracted directory of the backup-utils tarball
3. Create an RPM for installation of backup-utils, using the ``fpm`` tool:
   ``fpm -s dir -t rpm -n github-backup-utils -v 2.10.0 --prefix /opt``
4. Copy the RPM to ``/usr/rmt_share/packages/rcac_software/rhel6-software`` on
   ``yum.rcac`` and wait 15-20 minutes for the repository to update via cron
5. Run ``yum clean all`` on ``github-backups.rcac``
6. Change the version numbers in ``github_backup_utils/manifests/params.pp`` in
   Puppet
7. Run Puppet
