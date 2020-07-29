---
title: Overview of ${resource.name}
tags:
 - caesar
---
# Overview of ${resource.name}

Caesar was an SGI Altix 4700 system. This large memory SMP design featured 128 processors and 512 GB of RAM connected via SGI's high-bandwidth, low-latency NUMAlink shared-memory interface. The extremely large amount of shared memory in this system made it ideal for jobs where many processors must all share a large amount of in-memory data, and for large parallel jobs, using shared memory for fast communication between processors.

Detailed Hardware Specification

<table class="inrows">
	<thead>
		<tr>
			<th scope="col">Number of Nodes</th>
			<th scope="col">Processors per Node</th>
			<th scope="col">Cores per Node</th>
			<th scope="col">Memory per Node</th>
			<th scope="col">Retired in</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>1</td>
			<td>128 1.6 GHz Single-Core Intel Itanium 2</td>
			<td>128</td>
			<td>512 GB</td>
			<td>2010</td>
		</tr>
	</tbody>
</table>

Caesar also featured 33 TB of local scratch disk space.

Caesar ran SuSE Linux Enterprise Server 10 and used PBSPro 9.x for resource and job management. Caesar also ran jobs for BoilerGrid whenever processors in it would otherwise have been idle.
