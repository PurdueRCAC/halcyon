---
title: Overview of ${resource.name}
tags:
 - pete
---
# Overview of ${resource.name}

The Pete cluster was composed of two parts, one owned by Earth, Atmospheric, and Planetary Science (EAPS) and the other by the Network for Computational Nanotechnology (NCN). Pete consisted of 166 HP Dual-Processor Dual-Core DL40 systems with either 8 or 16 GB RAM and Gigabit Ethernet. The large amount of memory in this system for its time made it well suited for larger-memory parallel jobs.

<div class="inrows-wide">
	<table class="inrows-wide">
		<caption>Detailed Hardware Specification</caption>
		<tr>
			<th scope="col">Sub-Cluster</th>
			<th scope="col">Number of Nodes</th>
			<th scope="col">Processors per Node</th>
			<th scope="col">Cores per Node</th>
			<th scope="col">Memory per Node</th>
			<th scope="col">Retired in</th>
		</tr>
		<tr>
			<td>EAS</td>
			<td>84</td>
			<td>Two 2.33 GHz Dual-Core Intel Xeon E5140</td>
			<td>4</td>
			<td>8 GB</td>
			<td>2009</td>
		</tr>
		<tr>
			<td>NCN</td>
			<td>82</td>
			<td>Two 2.33 GHz Dual-Core Intel Xeon E5140</td>
			<td>4</td>
			<td>16 GB</td>
			<td>2009</td>
		</tr>
	</table>
</div>

Pete also featured a 17 TB NFS scratch filesystem and a 16 TB Lustre scratch filesystem.

All Pete nodes ran Red Hat Enterprise Linux 4 (RHEL4) and used PBSPro 9.x for resource and job management. Pete also ran jobs for BoilerGrid whenever processors in it would otherwise have been idle.
