---
title: Draining and Restoring Front-ends
tags:
 - internal
---

Front-ends that have died should be removed from service if server restoration
is not imminent.

If a front-end has died unexpectedly twice in a row, it should get investigated
before being returned to service.

# Removing a front-end from rotation on failure
1. Announce problem to #outage
2. Remove IP address from round-robin A record
    1. https://hostmaster.itap.purdue.edu
    2. Search for the hostname at the upper right
    3. Open one of them in a new tab; open the other in the current tab. For
       each tab:
        1. Click the double arrow drop-down and select Edit
        2. Click the "Remove" button for the IP and click update
        3. Click "rcac.purdue.edu" in the top nav view
        4. Click the "Action" button and select "Quick Deploy" and "Yes"
    3. Push DNS change inside of RCAC
        1. From root@caput: ``ssh root@manus-00 /root/clear_dns_cache.sh``
    4. Run ``host <cluster name>`` and make sure some IP addresses are still in
       there
3. Change LDAP host filter to xenon.rcac.purdue.edu
    1. Puppet: ``filter_host`` hiera key
    2. Halstead: Edit both locations in ``/etc/pam_ldap.conf``
    3. HalsteadGPU and newer: Disable Puppet, then edit the ``host`` field
       under ``pam_authz_search`` in ``/etc/nslcd.conf``, then restart ``nslcd``
4. Install ``/etc/issue``
    * For older clusters (before Halstead), uncomment the "issue" line in
      ``hieradata/hosts/<name>``
    * For Halstead, copy the issue message from another cluster into
      ``/etc/issue``
    * Message should note that the system is not available for use
5. Stop Globus
    1. ``service globus-gridftp-server stop``
6. Announce completion in #outage

# Draining a front-end for maintenance
1. Announce draining to #ops
2. Remove IP address from round-robin A record
    1. https://hostmaster.itap.purdue.edu
    2. Search for the hostname at the upper right
    3. Open one of them in a new tab; open the other in the current tab. For
       each tab:
        1. Click the double arrow drop-down and select Edit
        2. Click the "Remove" button for the IP and click update
        3. Click "rcac.purdue.edu" in the top nav view
        4. Click the "Action" button and select "Quick Deploy" and "Yes"
    3. Push DNS change inside of RCAC
        1. From root@caput: ``ssh root@manus-00 /root/clear_dns_cache.sh``
    4. Run ``host <cluster name>`` and make sure some IP addresses are still in
       there
3. Change LDAP host filter to xenon.rcac.purdue.edu
    1. Puppet: ``filter_host`` hiera key
    2. Halstead: Edit both locations in ``/etc/pam_ldap.conf``
    3. HalsteadGPU and newer: Disable Puppet, then edit the ``host`` field
       under ``pam_authz_search`` in ``/etc/nslcd.conf``, then restart ``nslcd``
4. Install ``/etc/issue``
    * For older clusters (before Halstead), uncomment the "issue" line in
      ``hieradata/hosts/<name>``
    * For Halstead, copy the issue message from another cluster into
      ``/etc/issue``
5. Stop Globus
    1. ``service globus-gridftp-server stop``
6. Begin shutdown procedure
    1. Wall users 24 hours in advance (preferably use one of the scripts)
        1. ``echo "message" | wall``
        2. ``/opt/thinlinc/sbin/tl-notify "message"``
    2. Wall users 12 hours in advance
        1. ``echo "message" | wall``
        2. ``/opt/thinlinc/sbin/tl-notify "message"``
    3. Launch ``shutdown -r +120`` two hours in advance
        1. Send one last ``tl-notify``
7. Announce task complete in #ops

# Returning a front-end to service
1. Boot front-end
    1. Ensure clock is accurate
    2. Ensure connectivity to PBS
    3. Ensure ``/homes``, ``/apps``, ``/depot``, and ``/scratch`` are available
2. Change LDAP host filter to <cluster>.rcac.purdue.edu
    1. **Note:** Rice has a compound ``filter_host`` for Scholar users
    2. Puppet: revert ``filter_host`` hiera change
    3. Halstead and newer: skip this step
3. Remove ``/etc/issue`` (on Halstead, skip this step)
4. Add IP address into round-robin A record
    1. https://hostmaster.itap.purdue.edu
    2. Search for the hostname at the upper right
    3. Open one of them in a new tab; open the other in the current tab. For
       each tab:
        1. Click the double arrow drop-down and select Edit
        2. Enter the IP address, click "Add Another", and click update
        3. Click "rcac.purdue.edu" in the top nav view
        4. Click the "Action" button and select "Quick Deploy" and "Yes"
    4. Push DNS change inside of RCAC
        1. From root@caput: ``ssh root@manus-00 /root/clear_dns_cache.sh``
    5. Run ``host <cluster name>`` and make sure the new IP address appears
5. Check Globus and start if needed
    1. ``service globus-gridftp-server status``
6. Announce change to #outage or #ops
