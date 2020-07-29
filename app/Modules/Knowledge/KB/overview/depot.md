---
title: ${resource.name} Overview
tags:
 - depot
---
# ${resource.name} Overview

The Data Depot is a high-capacity, fast, reliable and secure data storage service designed, configured and operated for the needs of Purdue researchers in any field and shareable with both on-campus and off-campus collaborators.

As with the community clusters, research labs will be able to easily purchase capacity in the Data Depot through the <a href="/purchase/depot/">Data Depot Purchase</a> page on this site.  For more information, please contact us at <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a>.

# Data Depot Features

<p>The Data Depot offers research groups in need of centralized data storage unique features and benefits:</p>
<ul class="feature_list">
	<li>
		Available
		<p>
			To any Purdue research group as a purchase in increments of 1 TB at a competitive annual price or you may <a href="/order/products/3">request a 100 GB trial space</a> free of charge. <strong>Participation in the Community Cluster program is not required.</strong>
		</p>
	</li>
	<li>
		Accessible
		<ul>
			<li>
				As a <a href="/knowledge/depot/storage/transfer/cifs">Windows or Mac OS X network drive</a> on personal and lab computers on campus.  
			</li>
			<li>
				Directly on Community Cluster nodes.
			<li>
				From other universities or labs through <a href="/knowledge/depot/storage/transfer/globus">Globus</a>.
			</li>
		</ul>
	</li>
	<li>
		Capable
		<p>
			The Data Depot facilitates joint work on shared files across your research group, avoiding the need for numerous copies of datasets across individuals' home or scratch directories. It is an ideal place to store group applications, tools, scripts, and documents.
		<p>
	</li>
	<li>
		Controllable Access
		<p>
			Access management is under your direct control. ITaP will create Unix groups for your group and assist you in setting appropriate permissions to allow exactly the access you want and prevent any you do not. Easily manage who has access through <a href="/account/user/">a simple web application</a> &#8212; the same application used to manage access to Community Cluster queues.
		</p>
	</li>
	<li>
		Data Retention
		<p>
			All data kept in the Data Depot remains owned by the research group's lead faculty.  When researchers or students leave your group, any files left in their home directories may become difficult to recover.  Files kept in Data Depot remain with the research group, unaffected by turnover, and could head off potentially difficult disputes.
		</p>
	</li>
	<li>
		Never Purged
		<p>
			The Data Depot is never subject to purging.
		</p>
	</li>
	<li>
		Reliable
		<p>
			The Data Depot is redundant and protected against hardware failures and accidental deletion.  All data is mirrored at two different sites on campus to provide for greater reliability and to protect against physical disasters.
		</p>
	</li>
	<li>
		Restricted Data
		<p>
			The Data Depot <strong>is</strong> suitable for non-HIPAA human subjects data. See the Data Depot <a href="/knowledge/depot/faq/data/humansubjectdata">FAQ</a> for a data security statement for your IRB documentation.  The Data Depot is <strong>not</strong> approved for regulated data, including HIPAA, ePHI, FISMA, or ITAR data.
		</p>
	</li>
</ul>

# Data Depot Hardware Details

The Data Depot uses an enterprise-class GPFS storage solution with an initial total capacity of over 2 PB.  This storage is redundant and reliable, features regular snapshots, and is globally available on all ITaP research systems.  The Data Depot is non-purged space suitable for tasks such as sharing data, editing files, developing and building software, and many other uses.  Built on Data Direct Networks' SFA12k storage platform, the Data Depot has redundant storage arrays in multiple campus datacenters for maximum availability.

<strong>While the Data Depot will scale well for most uses, ITaP continues to recommend using each cluster's parallel scratch filesystem for use as high-performance working space (scratch) for running jobs.</strong>
