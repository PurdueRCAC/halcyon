---
title: How do I use native mode on the Xeon Phi?
tags:
 - phi
---

### How do I use native mode on the Xeon Phi?

Native mode may be enabled in a job by adding <kbd>mics=2</kbd> to your job request (<kbd>-l nodes=1:ppn=16:mics=2</kbd> for example). The <kbd>mics</kbd> parameter will instruct PBS to initialize, reboot, and enable your user login on the Phis.

The environment on the Phis may be configured using <kbd>module</kbd> the same as the host compute node. There are several libaries available so far that have been compiled for the Phi.
