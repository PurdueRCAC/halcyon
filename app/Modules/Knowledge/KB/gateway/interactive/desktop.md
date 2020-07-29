---
title: Compute Node Desktop 
tags:
  - gateway
---

# Compute Node Desktop

The Compute Node Desktop app will launch a graphical desktop session on a compute node. This is similar to using [Thinlinc](../../accounts/login/thinlinc), however, this gives you a desktop directly on a compute node instead on a front-end. This app is useful if you have a custom application or application not directly available as an interactive app you would like to run inside Gateway.

To launch a desktop session on a compute node, select the ${resource.name} Compute Desktop app. From the submit form, select from the available options - the queue to which you wish to submit and the number of wallclock hours you wish to have job running. There is also a checkbox that enable a notification to your email when the job starts. 

After the interactive job is submitted you will be taken to your list of active interactive app sessions. You can monitor the status of the job from here until it starts, or if you enabled the email notification, watch your Purdue email for the notification the job has started.

Once it is indicated the job has started you can connect to the desktop with the "Launch noVNC in New Tab" button. The session will be terminated after the wallclock hours you specified have elapsed or you terminate the session early with the "Delete" button from the list of sessions. Deleting the session when you are finished will free up queue resources for your labmates and other users on the system.
