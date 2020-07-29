---
title: "I worked on ${resource.name} after I graduated/left Purdue, but can not access it anymore"
tags:
 - faq
 - internal
---

### Problem

You have graduated or left Purdue but continue collaboration with your Purdue colleagues. You find that your access to Purdue resources has suddenly stopped and your password is no longer accepted.

### Solution

Access to all Research Computing resources depends on having a valid Purdue Career Account. Expired Career Accounts are removed twice a year, during Spring and October breaks (more details at the [official page](https://www.purdue.edu/apps/account/IAMO/Purdue_CareerAccount_Expiration.jsp)).  If your Career Account was purged due to expiration, you will not be be able to access the resources.

To provide remote collaborators with valid Purdue credentials, the University provides a special procedure called [R4P ("request for privileges")](http://www.purdue.edu/hr/HR_Operations/SysInfo/requestForPrivilegesDoc.html).  If you need to continue your collaboration with your Purdue PI, the PI will have to work with their departmental Business Office to submit or renew an R4P request on your behalf.

After your R4P is completed and Career Account is restored, please note two additional necessary steps:

* **Access:** Restored Career Accounts by default do **not** have any Research Computing resources enabled for them. **Your PI will have to login to the [Manage Users](/account/user) tool and explicitly re-enable your access by un-checking and then ticking back checkboxes for desired queues/Unix groups resources.**

* **Email:** Restored Career Accounts by default do **not** have their _@purdue.edu_
email service enabled.  While this does not preclude you from using Research
Computing resources, any email messages (be that generated on the clusters, or
any service announcements) would not be delivered - which may cause
inconvenience or loss of compute jobs.  To avoid this, we recommend setting
your restored _@purdue.edu_ email service to "Forward" (to an actual address
you read).  The easiest way to ensure it is to go through the [Account Setup
process](https://www.purdue.edu/apps/account/AccountSetup).

{::if user.staff == 1}
### Staff Notes

1. Potentially confusing elements here are:
   * The error message looks like an incorrect password, because SSH does not divulge any indications about account validity.
   * The user still exists in our LDAP (so `ldapsearch` happily shows all the hosts).
   * The expired career account still exists in RCAC database (so [Manage Users](/account/user) web tool happily shows the user with all the checkboxes in place).

2. Dead giveaway clues:
   * _"No role"_ shows for all resources in the the [User Search tool](/admin/user/).
   * _"User not found"_ in [Purdue Directory](https://www.purdue.edu/directory/).

3. **The _"uncheck, then check back"_ trick is required**. Yes, for every queue and Unix group.

{::/}
