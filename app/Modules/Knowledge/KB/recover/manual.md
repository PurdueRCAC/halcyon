---
title: Manual Browsing
tags:
 - depot
 - home
---

# Manual Browsing

You may also search through the snapshots by hand on the ${resource.name} filesystem if you are not sure what date you lost the file or would like to browse by hand. Snapshots are browsable from any ITaP Research Computing resource. If you do not have access to a compute cluster, any ${resource.name} user may use an SSH client to connect to <kbd>${resource.hostname}.rcac.purdue.edu</kbd> and browse from there. The snapshots are located at `/depot/.snapshots` on these resources.

You can also mount the snapshot directory over Samba (or SMB, CIFS) on Windows or Mac OS X. Mount (or map) the snapshot directory in the [same way](../../storage/transfer/cifs) as you did for your main ${resource.name} space substituting the server name and path for `\\datadepot.rcac.purdue.edu\depot\.winsnaps` (Windows) or `smb://datadepot.rcac.purdue.edu/depot/.winsnaps` (Mac OS X).

Once connected to the snapshot directory through SSH or Samba you will see something similar to this:

<table class="inrows-wide">
<caption>Snapshots folders may look slightly differently when accessed via SSH on `${resource.hostname}.rcac.purdue.edu` or via Samba on datadepot.rcac.purdue.edu. Here are examples of both.</caption>
	<tr>
		<th scope="col" style="text-align: center;">SSH to <kbd>${resource.hostname}.rcac.purdue.edu</kbd></th>
		<th scope="col" style="text-align: center;">Samba mount on <kbd>datadepot.rcac.purdue.edu</kbd></th>
	</tr>
	<tr>
		<td style="vertical-align: top;">
<pre>
$ cd /depot/.snapshots
$ ls -1
daily_20190129000501
daily_20190130000501
daily_20190131000502
daily_20190201000501
daily_20190202000501
daily_20190203000501
daily_20190204000501
monthly_20181101001501
monthly_20181201001501
monthly_20190101001501
monthly_20190201001501
weekly_20190113002501
weekly_20190120002501
weekly_20190127002501
weekly_20190203002501
</pre>
		</td>
		<td style="vertical-align: top; text-align: center;">
<img src="/knowledge/downloads/recover/images/depot_smb_snapshots.png" alt="${resource.name} snapshots via Samba" width="350" />
		</td>
	</tr>
</table>

Each of these directories is a snapshot of the entire ${resource.name} filesystem at the timestamp encoded into the directory name. The format for this timestamp is year, two digits for month, two digits for day, followed by the time of the day.

You may <kbd>cd</kbd> into any of these directories where you will find the entire ${resource.name} filesystem. Use <kbd>cd</kbd> to continue into your lab's ${resource.name} space and then you may browse the snapshot as normal.

If you are browsing these directories over a Samba network drive you can simply drag and drop the files over into your live Data Depot folder.

Once you find the file you are looking for, use <kbd>cp</kbd> to copy the file back into your lab's live ${resource.name} space. <strong>Do not attempt to modify files directly in the snapshot directories.</strong>
