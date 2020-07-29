---
title: Account creation failed
tags:
 - internal
---

### Account creation failed

An email came into rcac-help from the automated account checker that an account creation failed. There are a few scenarios that can cause this. There are a few things to check.

### Account not created
First check what resource they were added to and the corresponding role status from the User Search page. 

Take the following steps for these scenarios:
### No Role
This means either our website failed and didn't add the role (rare, but there is a known bug where when a faculty requests Radon/Hathi for themselves it fails) or IAMO rejected the role. 

You can try manually adding the role through the tool and see if it rejects it again, or ask IAMO about the status and if the role can be added (see below).

### Role Pending
This means two things: IAMO's overnight process failed or the account was added just past the cutoff for the overnight process, but before the account check run. 

In the former scenario, something went wrong on IAMO's side. Usually Ben is on top of things and gets things sorted quickly when he gets in the morning, but if it's afternoon and it's still not there ask IAMO about it.

For the latter scenario, there is a very narrow window when users can be added and trigger a false alarm (something like ~4-5am). It's rare, but it happens from time to time when we have a night owl/early bird faculty (or traveling abroad).

### Role Ready
The are two scenarios here: IAMO's overnight process failed and has already been fixed or the transd is broken on our end.

In the first scenario, there probably isn't anything to do. You can verify their account with `ldapsearch -x uid=USERNAME | grep host` and see if the have the proper host entry. If they do, they should be able to log in.

In the second scenario, the next step would be to investigate the transd. The transd translates packets from IAMO into accounts on our systems. Log into xenon.rcac and look at /var/log/transd_log. Is there recent activity at the end of log? If the end of the log is stale, something is probably stuck, like a full disk or some such. In this case, assign ticket to systems and ask them to look at it. If it has recent activty, you should be able to grep the log for the username and look for account entries for them. If the transd is running further investigation is probably needed.

### Asking IAMO

The Footprints queue for IAMO is ITAP_IDENTITY_MANAGEMENT. Ben Lewis and Scott Morris are familiar with our web app, and should be familiar with seeing this "account failed" emails. If they come back and say the account is expired/graduated/etc contact the faculty separately with this information (see below). Otherwise Ben should be able to push accounts or unjam the logjam. 

### Login Shell /opt/acmaint-3.10/etc/disable is invalid.

This means the user account is no longer valid, ie, they graduated. Remove the account from the Manage User page, and inform the faculty separately (don't use the FP ticket) that added them that we were unable to create an account for the user. Good to verify with PI about student's graudation status (usually that'll ring some bells with the faculty). They will need to have an R4P filed, and then they can re-add the account once complete. If the faculty thinks the student should be valid, ask IAMO about the status. They may have been very recently added back, or had some other issue.
