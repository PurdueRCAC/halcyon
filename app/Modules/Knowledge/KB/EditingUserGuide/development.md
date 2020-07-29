---
title: Development Process Guide
tags:
 - internal
---

# Development Process Guide

There are two modes to editing on the User Guide - quick editing and extensive editing. Each has a different development process that should be followed. There are also two ways files can be edited - through a repo clone or on the GitHub website.

### Editing Files

If you wish to edit from the command line or your own editor, you'll need to make a git clone of the repo. The address for cloning the master or any other branch can be found [on the main GitHub repo page](https://github.rcac.purdue.edu/RCAC-Staff/KB). Once cloned, files can be edit as normal, then added, committed, and pushed. 

You can also edit files directly on the [GitHub website](https://github.rcac.purdue.edu/RCAC-Staff/KB). Just browse through the files, find the file you want to edit, click it, and then click the edit button. The file will open in a browser editor. Once you're ready to commit, **please add a useful commit message** first (the default is very not descriptive) and click the Commit button. That's it.

### Quick Editing

For quick edits, such as fixing typos or incorrect information - generally edits involving no more than a couple sentennces - can be done directly on the master branch live. These changes go live within a few seconds, so these types of edits shouldn't involve anything controversial or extensinve. Fixing typos and the like should be safe to do live (and generally you'd want the fast turnaround in that case).

### Extensive Edit

For more extensive edits that require multiple commits, development, testing/review you should **not** do these live for obvious reasons. You should create a development branch for this particular modification (branches in git are typically per-task/improvement/project, rather than by person) to make your edits on. Then the changes on the branch can be reviewed, tweaked, and then merged into the master (live) branch by a pull request.

First start by creating a new branch. You can do this on the GitHub website. Click the "Branch: master" box. In the box type in a new name for this branch (again, this should be by task, not by person). It will create a new branch based on the current master. This is a new copy of the guide. You can then create your edits here. 

You can preview them on the website by going to the User Guide and logging in. Anyone who can manage news will be able to see a selector for viewing the branch. You can select your branch and view it. It should look exactly the same as your live site, except with your edits. The pages will be slower as it is pulling the pages from the GitHub API rather than the local cache for the live guide.

Once you're done, and want to propose that your edits be pushed to the live site, go to the GitHUb page, and select "Create Pull Request". Select master on the ??? side, and your branch on the ??? side. It will display the diffs for you to see what changes this would make. Give the pull request a descriptive name and description and create it. Send the link to the pull request to the web mailing list for review. If you get the OK to make this change you can go back to this page and merge the pull request to make the changes live. Once complete (and you're sure you're done making changes for this task), please delete your branch from the branches list. Again, branches should be per task and not per person. You should *not* have a personally name development branch. We also don't want to have a bunch of cruft branches laying around.
