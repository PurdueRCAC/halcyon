---
title: Check Account Deletion
tags:
 - internal
---

# Check Account Deletion

When IAMO deletes an account from our LDAP, it generates a transd packet. Account deletions can be tracked from Splunk using the search string `"host=xenon* transd"`. 

There is a helpful dashboard that will let you search for deleted accounts: <a target="_blank" rel="noopener" href="https://splunk.rcac.purdue.edu/en-US/app/search/account_purge">Account Purge Dashboard</a>. Select an appropriate time duration and enter the username in `"Select User"`. This will show you when the account was deleted and on which cluster.

Note that even if accounts are deleted, users' roles may still exist in our database. Therefore, checking users' roles from website may be unreliable after accounts are purged. If you are still unsure about the account status, send a ticket to IAMO.
