---
title: Overview of ${resource.name}
tags:
 - prospero
---
# Overview of ${resource.name}

The Prospero community cluster consisted of 19 Dell Quad-Processor 2.33 GHz Intel Xeon systems with 8 GB RAM and both Gigabit Ethernet and Infiniband interconnects. Each node had enough memory to run most jobs, and the high-speed Infiniband interconnect helped with many communication-bound parallel jobs.

<div class="inrows-wide">
	<table class="inrows-wide">
		<caption>Detailed Hardware Specification</caption>
		<tr>
			<th scope="col">Number of Nodes</th>
			<th scope="col">Processors per Node</th>
			<th scope="col">Cores per Node</th>
			<th scope="col">Memory per Node</th>
			<th scope="col">Retired in</th>
		</tr>
		<tr>
			<td>19</td>
			<td>Four 2.33 GHz Single-Core Intel Xeon 5140</td>
			<td>4</td>
			<td>8 GB</td>
			<td>2013</td>
		</tr>
	</table>
</div>

All Prospero nodes ran Red Hat Enterprise Linux 4 (RHEL4) and used PBSPro 9.x for resource and job management. Prospero also ran jobs for BoilerGrid whenever processors in it would otherwise have been idle.
