---
title: What sort of performance should I expect to and from the ${resource.name}?
tags:
 - depot
---

### What sort of performance should I expect to and from the ${resource.name}?

<table class="inrows-wide">
	<caption>The ${resource.name} is designed to be a high-capacity, fast, reliable and secure data storage system for research data. During acceptance testing, a number of performance baselines were measured:</caption>
	<tr>
		<th scope="col">Access type</th>
		<th scope="col">Large file, reading</th>
		<th scope="col">Large file, writing</th>
		<th scope="col">Many small files, reading</th>
		<th scope="col">Many small files, writing</th>
	</tr>
	<tr>
		<th scope="row">CIFS access, single client (GigE)</th>
		<td>102.1 MB/sec</td>
		<td>71.64 MB/sec</td>
		<td>12.43 MB/sec</td>
		<td>11.57 MB/sec</td>
	</tr>
</table>
