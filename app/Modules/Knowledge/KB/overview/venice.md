---
title: Overview of ${resource.name}
tags:
 - venice
---
# Overview of ${resource.name}

Venice was a small cluster of Sun x4600 systems consisting of two front-end nodes and three compute nodes. The front-end nodes were both a Quad-Processor Dual-Core AMD Opteron 2216. The compute nodes were each an Eight-Processor Dual-Core AMD Opteron 8220 with 128 GB of RAM. The large amount of shared memory in this system made it ideal for large parallel jobs, using shared memory for fast communication between processor cores.

Detailed Hardware Specification

<table class="inrows-wide">
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
			<td>3</td>
			<td>Eight 1.0 GHz Dual-Core AMD Opteron 8220</td>
			<td>16</td>
			<td>128 GB</td>
			<td>2013</td>
		</tr>
	</tbody>
</table>

All Venice nodes ran Red Hat Enterprise Linux 4 (RHEL4) and used PBSPro 9.x for resource and job management. Venice also ran jobs for BoilerGrid whenever processors in it would otherwise have been idle.
