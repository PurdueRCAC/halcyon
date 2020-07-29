---
title: Overview of ${resource.name}
tags:
 - coates
---
# Overview of ${resource.name}

Coates was a compute cluster operated by ITaP and was a member of Purdue's Community Cluster Program. ITaP installed Coates on July 21, 2009, and at the time it was the largest entirely 10 Gigabit Ethernet (10GigE) academic cluster in the world. Coates consisted of 982 64-bit, 8-core Hewlett-Packard Proliant and 11 64-bit, 16-core Hewlett-Packard Proliant DL585 G5 systems with between 16 GB and 128 GB of memory. All nodes had 10 Gigabit Ethernet interconnects and a 5-year warranty. Coates was decommissioned on September 30, 2014.

<div class="inrows-wide">
	<table class="inrows-wide">
		<caption>Detailed Hardware Specification</caption>
		<tr>
			<th scope="col">Sub-Cluster</th>
			<th scope="col">Number of Nodes</th>
			<th scope="col">Processors per Node</th>
			<th scope="col">Cores per Node</th>
			<th scope="col">Memory per Node</th>
			<th scope="col">Retired on</th>
		</tr>
		<tr>
			<td>A</td>
			<td class="numeric">640</td>
			<td>Two 2.5 GHz Quad-Core AMD 2380</td>
			<td class="numeric">8</td>
			<td class="numeric">32 GB</td>
			<td class="numeric">2014</td>
		</tr>
		<tr>
			<td>B</td>
			<td class="numeric">45</td>
			<td>Two 2.5 GHz Quad-Core AMD 2380</td>
			<td class="numeric">8</td>
			<td class="numeric">32 GB</td>
			<td class="numeric">2014</td>
		</tr>
		<tr>
			<td>C</td>
			<td class="numeric">264</td>
			<td>Two 2.5 GHz Quad-Core AMD 2380</td>
			<td class="numeric">8</td>
			<td class="numeric">16 GB</td>
			<td class="numeric">2014</td>
		</tr>
		<tr>
			<td>D</td>
			<td class="numeric">33</td>
			<td>Two 2.5 GHz Quad-Core AMD 2380</td>
			<td class="numeric">8</td>
			<td class="numeric">16 GB</td>
			<td class="numeric">2014</td>
		</tr>
		<tr>
			<td>E</td>
			<td class="numeric">11</td>
			<td>Four 2.5 GHz Quad-Core AMD 8380</td>
			<td class="numeric">16</td>
			<td class="numeric">128 GB</td>
			<td class="numeric">2014</td> 
		</tr>
	</table>
</div>

Coates nodes ran Red Hat Enterprise Linux 5 (RHEL5) and used Moab Workload Manager 7 and TORQUE Resource Manager 4 as the portable batch system (PBS) for resource and job management. Coates also ran jobs for BoilerGrid whenever processor cores in it would otherwise have been idle.
