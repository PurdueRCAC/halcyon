---
title: Jobs
tags:
  - gateway
---

# Jobs

There are two apps under the Jobs apps: Active Jobs and Job Composer. These are detailed below.

## Active Jobs

This shows you active PBS jobs currently on the cluster. The default view will show you your current jobs, similar to `qstat -u ${user.username}`. Using the button labeled "Your Jobs" in the upper right allows you to select different filters by queue. All queues output by `qlist` will appear for you here. Using the arrow on the left hand side will expand the full job details.

<img src="/knowledge/downloads/gateway/images/myjobs.png" alt="My jobs" />

## Job Composer

The Job Composer app allows you to create and submit jobs to the cluster. You can select from pre-defined templates (most of these are taken from the User Guide examples) or you can create your own templates for frequently used workflows.

### Creating Job from Existing Template

Click "New Job" menu, then select "From Template":

<img src="/knowledge/downloads/gateway/images/jobcomposer1.png" alt="Job composer screenshot 1" />

Then select from one of the available templates. 

<img src="/knowledge/downloads/gateway/images/jobcomposer2.png" alt="Job composer screenshot 2" />

Click 'Create New Job' in second pane.

<img src="/knowledge/downloads/gateway/images/jobcomposer3.png" alt="Job composer screenshot 3" />

Your new job should be selected in your list of jobs. In the 'Submit Script' pane you can see the job script that was generated with an 'Open Editor' link to open the script in the built-in editor. Open the file in the editor and edit the script as necessary. By default the job will specify standby queue - this should be changed as appropriate, along with the node and walltime requests.

<img src="/knowledge/downloads/gateway/images/jobcomposer4.png" alt="Job composer screenshot 4" />

When you are finished with editing the job and are ready to submit, click the green 'Submit' button at the top of the job list. You can monitor progress from here or from the Active Jobs app. Once completed, you should see the output files appear:

<img src="/knowledge/downloads/gateway/images/jobcomposer5.png" alt="Job composer screenshot 5" />

Clicking on one of the output files will open it in the file editor for your viewing.

### Creating New Template

First, prepare a template directory containing a template submission script along with any input files. Then, to import the job into the Job Composer app, click the 'Create New Template' button. Fill in the directory containing your template job script and files in the first box. Give it an appropriate name and notes.

<img src="/knowledge/downloads/gateway/images/jobcomposer6.png" alt="Job composer screenshot 6" />

This template will now appear in your list of templates to choose from when composing jobs. You can now go create and submit a job from this new template.
