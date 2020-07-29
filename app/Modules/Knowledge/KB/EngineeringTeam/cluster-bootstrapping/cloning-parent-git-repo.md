---
title: Cloning the Base Git Repository
tags:
 - internal
---

All of our new clusters (HalsteadGPU, Brown, and newer) will be based on the
most recent cluster, so there will be a lineage of sorts. For example, Brown is
a child of HalsteadGPU, and whatever comes after Brown will be a child of Brown
and a grandchild of HalsteadGPU.

This document describes the process for creating this lineage when bootstrapping
a new cluster.

# Cloning the parent repository
1. Create a blank repository on GitHub named ``xCAT-<cluster
   name>-Configuration``
    1. For this example, "Brown" will be the cluster name.
2. ``cp -R xCAT-HalsteadGPU-Configuration/ xCAT-Brown-Configuration``
3. ``cd xCAT-Brown-Configuration``
4. ``git remote set-url origin git@github.rcac.purdue.edu:RCAC-Staff/xCAT-Brown-Configuration.git``
5. ``git push --force``
6. Clean up old branches:
    1. List the existing branches with ``git branch -a``
    2. To remove old deployed branches, run ``git branch -d -r
        origin/deployed-<date>``

# Updating cluster configuration
Do _NOT_ blindly search-and-replace `<old cluster name>` to `<new cluster name>`

You must audit the configuration and manually modify the configuration as
needed. This is most easily accomplished by looping through ``ack`` and changing
some files.

This command is your friend: ``ack --ignore-file=is:'README.md' halsteadgpu``

When finished, commit the changes and push them upstream.
