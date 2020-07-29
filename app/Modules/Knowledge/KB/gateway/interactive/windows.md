---
title: Windows Desktop 
tags:
  - gateway
---

# Windows Desktop

The Windows Desktop app will launch a Windows desktop session on a compute node. This is similar to using the Windows menu launcher through [Thinlinc](../../../accounts/login/thinlinc), however, this gives you a Windows desktop directly on a compute node instead on a front-end. 

To launch a Windows session on a compute node, select the Windows Desktop app. From the submit form, select from the available options - choose from the basic Windows configuration or the GIS configured image, the queue to which you wish to submit, and the number of wallclock hours you wish to have job running. There is also a checkbox that enable a notification to your email when the job starts.

This will create a file in your scratch space called `windows-base.qcow2` or `windows-gis.qcow2`. If the file already exists, the existing image will be restarted. You can delete or rename the image at any time through the [Files App](../../files) to generate a fresh image. You can only have one instance of the image running at a time or corruption will occur. There are lock files to prevent this, but be mindful of this restriction. It is also recommended you make periodic backups of the image if you are making any modifications to it. 

After the interactive job is submitted you will be taken to your list of active interactive app sessions. You can monitor the status of the job from here until it starts, or if you enabled the email notification, watch your Purdue email for the notification the job has started.

Once it is indicated the job has started you can connect to the desktop with the "Launch noVNC in New Tab" button. The session will be terminated after the wallclock hours you specified have elapsed or you terminate the session early with the "Delete" button from the list of sessions. Deleting the session when you are finished will free up queue resources for your labmates and other users on the system.

