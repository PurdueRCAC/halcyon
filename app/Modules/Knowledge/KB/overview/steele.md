---
title: Overview of ${resource.name}
tags:
 - steele
---
# Overview of ${resource.name}

Steele was a compute cluster operated by ITaP and the first system built under Purdue's Community Cluster Program. ITaP installed Steele in May 2008 in an unprecedented single-day installation. It replaced and expanded upon ITaP research resources retired at the same time, including the Hamlet, Lear, and Macbeth clusters. Steele consisted of 852 64-bit, 8-core Dell 1950 and 9 64-bit, 8-core Dell 2950 systems with various combinations of 16-32 GB RAM, 160 GB to 2 TB of disk, and 1 Gigabit Ethernet (1GigE) and InfiniBand local to each node.

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
			<td>B</td>
			<td class="numeric">180</td>
			<td>Two 2.33 GHz Quad-Core Intel E5410</td>
			<td class="numeric">8</td>
			<td class="numeric">16 GB</td>
			<td class="numeric">2013</td>
		</tr>
		<tr>
			<td>C</td>
			<td class="numeric">48</td>
			<td>Two 2.33 GHz Quad-Core Intel E5410</td>
			<td class="numeric">8</td>
			<td class="numeric">32 GB</td>
			<td class="numeric">2013</td>
		</tr>
		<tr>
			<td>D</td>
			<td class="numeric">41</td>
			<td>Two 2.33 GHz Quad-Core Intel E5410</td>
			<td class="numeric">8</td>
			<td class="numeric">32 GB</td>
			<td class="numeric">2013</td>
		</tr>
		<tr>
			<td>E</td>
			<td class="numeric">9</td>
			<td>Two 3.00 GHz Quad-Core Intel E5450</td>
			<td class="numeric">8</td>
			<td class="numeric">32 GB</td>
			<td class="numeric">2013</td>
		</tr>
		<tr>
			<td>Z</td>
			<td class="numeric">48</td>
			<td>Two 2.33 GHz Quad-Core Intel E5410</td>
			<td class="numeric">8</td>
			<td class="numeric">16 GB</td>
			<td class="numeric">2013</td>
		</tr>
	</tbody>
</table>

At the time of retirement, Steele nodes ran Red Hat Enterprise Linux 5 (RHEL5) and used Moab Workload Manager 7 and TORQUE Resource Manager 4 as the portable batch system (PBS) for resource and job management. Steele also ran jobs for BoilerGrid whenever processor cores in it would otherwise be idle.
