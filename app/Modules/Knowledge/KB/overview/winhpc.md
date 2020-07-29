---
title: Overview of ${resource.name}
tags:
 - winhpc
---
# Overview of ${resource.name}

WinHPC was a compute cluster operated by ITaP, and a member of Purdue's Community Cluster Program. WinHPC went into production on December 1, 2011. WinHPC consisted of HP compute nodes with two 12-core AMD Opteron 6172 processors (24 cores per node) and 48 GB of memory. All nodes had 10 Gigabit Ethernet interconnects and a 5-year warranty. WinHPC was decommissioned on October 1, 2016.

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
			<td class="numeric">12</td>
			<td>Two 2.3 GHz 12-Core AMD 6176</td>
			<td class="numeric">24</td>
			<td class="numeric">48 GB</td>
			<td class="numeric">2016</td>
		</tr>
	</tbody>
</table>

WinHPC nodes ran Windows HPC 2008 R2 and used the Windows HPC Job Manager for resource and job management. The application of operating system patches occurred as security needs dictated. All nodes allowed for unlimited stack usage, as well as unlimited core dump size (though disk space and server quotas were still a limiting factor).
