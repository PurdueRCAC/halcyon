---
title: Overview of ${resource.name}
tags:
 - brutus
---
# Overview of ${resource.name}

Brutus was an experimental FPGA resource provided by the Northwest Indiana Compuational Grid (NWICG) through ITaP. Brutus consisted of an SGI Altix 450 with two SGI RC100 blades with two FPGAs each, for a total of 4 FPGAs. Using Brutus effectively required careful code development in either VHDL or Mitrion-C, but did result in significant performance increases. BLAST was been benchmarked on Brutus at 70x typical general-purpose CPU performance.

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
			<td>2</td>
			<td>One Virtex 4 LX200 Dual-FPGA</td>
			<td>2</td>
			<td>80 MB QDR SDRAM</td>
			<td>2010</td>
		</tr>
	</table>
</div>

Brutus ran SuSE Linux Enterprise Server 10 and used Condor for resource and job management.

FPGA algorithms were serialized, placed, and routed (analogous to compilation) and then registered for use by Brutus jobs from the frontend node portia.rcac.purdue.edu, from which jobs were also submitted. Mitrionics' Mitrion-C compiler for FPGA algorithm development was provided, as well as Xilinx VHDL tools. Pre-SPRed bitcode could also be used. Some algorithms, such as BLAST, were provided pre-installed by ITaP.
