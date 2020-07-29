---
title: "cannot connect to X server / cannot open display"
tags:
 - faq
 - internal
---

### Problem

You receive the following message after entering a command to bring up a graphical window 

`cannot connect to X server`
`cannot open display`

### Solution

This can happen due to multiple reasons:
 1. Reason: Your SSH client software does not support graphical display by itself (e.g. SecureCRT or PuTTY).
	* Solution: Try using a client software like Thinlinc or MobaXterm as described [here](../../../../accounts/login/x11).
 2. Reason: You did not enable X11 forwarding in your SSH connection.
	* Solution: If you are in a Windows environment, make sure that X11 forwarding is enabled in your connection settings (e.g. in MobaXterm or PuTTY). If you are in a Linux environment, try  

		`ssh -Y -l username hostname`
{::if resource.batchsystem != none}
 3. Reason: If you are trying to open a graphical window within an interactive PBS job, make sure you are using the `-X` option with `qsub` after following the previous step(s) for connecting to the front-end. Please see the example [here](../../../../run/examples/pbs/interactive).
{::/}
 4. Reason: If none of the above apply, make sure that you are within quota of your home directory as described [here](../../../login/ErrorLockingAuthFile).
