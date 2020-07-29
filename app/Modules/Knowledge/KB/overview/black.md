---
title: Overview of ${resource.name}
tags:
 - black
---
# Overview of ${resource.name}

The Black cluster was Purdue's portion of the Indiana Economic Development Corporation (IEDC) machine at Indiana University, the IU portion of which was known as 'Big Red'. Black consisted of 256 IBM JS21 Blades, each a Dual-Processor 2.5 GHz Dual-Core PowerPC 970 MP with 8 GB of RAM and PCI-X Myrinet 2000 interconnects. The large amount of shared memory in this system provided very fast communication between processor cores via shared memory and made the system ideal for large parallel jobs.

<div class="inrows-wide">
	<table class="inrows-wide">
		<caption>Detailed Hardware Specifications</caption>
		<tr>
			<th scope="col">Number of Nodes</th>
			<th scope="col">Processors per Node</th>
			<th scope="col">Cores per Node</th>
			<th scope="col">Memory per Node</th>
			<th scope="col">Retired in</th>
		</tr>
		<tr>
			<td class="numeric">256</td>
			<td>Two 2.5 GHz Dual-Core PowerPC 970MP</td>
			<td class="numeric">4</td>
			<td class="numeric">8 GB</td>
			<td class="numeric">2010</td>
		</tr>
	</table>
</div>

Aside from Myrinet, Black nodes were also connected by Gigabit Ethernet to a 266 TB GPFS filesystem, hosted on 16 IBM p505 Power5 systems.

All Black nodes ran SuSE Linux Enterprise Server 9 and used LoadLeveler 3.4.0 and Moab for resource and job management.
