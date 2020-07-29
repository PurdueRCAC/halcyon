---
title: Overview of ${resource.name}
tags:
 - gray
---

# Overview of ${resource.name}

The Gray cluster was solely a development platform to be used alongside the Indiana Economic Development Corporation (IEDC) machine Black. Gray was a place to compile code (mostly serial Condor applications) that were to be run on Black. Black is housed with Indiana University's "Big Red" system in Bloomington, Indiana. However, Gray was located on Purdue's West Lafayette campus. Gray included a front-end server, several worker-node blades, and extra front-end hosts for campus and TeraGrid Condor users.

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
			<td>4</td>
			<td>Two 2.3 GHz Dual-Core PowerPC 970MP</td>
			<td>4</td>
			<td>8 GB</td>
			<td>2010</td>
		</tr>
	</table>
</div>

All Gray nodes ran SuSE Linux Enterprise Server 9. There was no job scheduling system, as Gray was to be used only for source code compilation.
