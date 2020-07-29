---
title: Overview of ${resource.name}
tags:
 - rossmann
---
# Overview of ${resource.name}

Rossmann was a compute cluster operated by ITaP and was a member of Purdue's Community Cluster Program. Rossmann went into production on September 1, 2010. It consisted of HP (Hewlett Packard) ProLiant DL165 G7 nodes with 64-bit, dual 12-core AMD Opteron 6172 processors (24 cores per node) and 48 GB, 96 GB, or 192 GB of memory. All nodes had 10 Gigabit Ethernet interconnects and a 5-year warranty. Rossmann was decommissioned on November 2nd, 2015.

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
			<td class="numeric">392</td>
			<td>Two 2.1 GHz 12-Core AMD 6172</td>
			<td class="numeric">24</td>
			<td class="numeric">48 GB</td>
			<td class="numeric">2015</td>
		</tr>
		<tr>
			<td>B</td>
			<td class="numeric">40</td>
			<td>Two 2.1 GHz 12-Core AMD 6172</td>
			<td class="numeric">24</td>
			<td class="numeric">96 GB</td>
			<td class="numeric">2015</td>
		</tr>
		<tr>
			<td>C</td>
			<td class="numeric">2</td>
			<td>Two 2.1 GHz 12-Core AMD 6172</td>
			<td class="numeric">24</td>
			<td class="numeric">192 GB</td>
			<td class="numeric">2015</td>
		</tr>
		<tr>
			<td>D</td>
			<td class="numeric">4</td>
			<td>Two 2.1 GHz 12-Core AMD 6172</td>
			<td class="numeric">24</td>
			<td class="numeric">192 GB</td>
			<td class="numeric">2015</td>
		</tr>
	</tbody>
</table>

Rossmann nodes ran Red Hat Enterprise Linux 6 (RHEL6) and used Moab Workload Manager 8 and TORQUE Resource Manager 5 as the portable batch system (PBS) for resource and job management. Rossmann also ran jobs for BoilerGrid whenever processor cores in it would otherwise be idle. The application of operating system patches occurred as security needs dictated. All nodes allowed for unlimited stack usage, as well as unlimited core dump size (though disk space and server quotas were still a limiting factor).
