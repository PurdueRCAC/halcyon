---
title: Overview of ${resource.name}
tags:
 - miner
---
# Overview of ${resource.name}

Miner was a compute cluster installed at the Purdue Calumet campus on December 25, 2009 and operated by ITaP. It was the first major research cluster on the Calumet campus and represented a great step forward in Purdue Calumet's ongoing plan to foster more local, cutting-edge research. Miner consisted of 512 2-core Intel Xeon systems with either 4 or 6 GB RAM, 50 GB of disk, and 1 Gigabit Ethernet (1GigE) local to each node.

Detailed Hardware Specification

<table class="inrows-wide">
	<thead>
		<tr>
			<th scope="col">Sub-Cluster</th>
			<th scope="col">Number of Nodes</th>
			<th scope="col">Processors per Node</th>
			<th scope="col">Cores per Node</th>
			<th scope="col">Memory per Node</th>
			<th scope="col">Retired in</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>A</td>
			<td class="numeric">500</td>
			<td>Two 3.2 GHz Single-Core Intel Xeon</td>
			<td class="numeric">2</td>
			<td class="numeric">4 or 6 GB</td>
			<td class="numeric">2012</td>
		</tr>
	</tbody>
</table>

Miner nodes ran Red Hat Enterprise Linux 5 (RHEL5) and used Portable Batch System Professional 11 (PBSPro 11) as the portable batch system (PBS) for resource and job management. Miner also ran jobs for BoilerGrid whenever processor cores in it would otherwise have been idle.
