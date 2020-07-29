---
title: Overview of ${resource.name}
tags:
 - moffett
---
# Overview of ${resource.name}

Moffett was a SiCortex 5832 system. It consisted of 28 modules, each containing 27 six-processor SMP nodes for a total of 4536 processor cores. The SiCortex design was highly unusual; it paired relatively slow individual processor cores (633 MHz) with an extraordinarily fast custom interconnect fabric, and provided these in very large numbers. In addition, the SiCortex design used very little power and thereby generated very little heat.

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
			<td class="numeric">756</td>
			<td>One 633 MHz Six-Core SiCortex 5832</td>
			<td class="numeric">6</td>
			<td class="numeric">8 GB</td>
			<td class="numeric">2013</td>
		</tr>
	</table>
</div>

All Moffett nodes ran Linux kernel version 2 and used SLURM and Maui for resource and job management.
