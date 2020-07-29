---
title: Accounts on Fortress
tags:
 - fortress
---
# Accounts on ${resource.name}

### Obtaining an Account

All Purdue faculty, staff, and students participating in the Community Cluster program have access to ${resource.name} along with their cluster nodes and scratch space.

Research groups are assigned a group data storage space within ${resource.name} with each Data Depot group space. Faculty should <a href="/storage/depot">request a Data Depot trial</a> to create a shared Fortress space for their research group.

<strong>ITaP research computing resources are not intended to store data protected by Federal privacy and security laws (e.g., HIPAA, ITAR, classified, etc.).  It is the responsibility of the faculty partner to ensure that no protected data is stored on the systems.</strong>
	<ul style="margin-bottom: 0;">
		<li>Particularly in the case of group storage, please keep in mind that such spaces are, by design, accessible by others and should not be used to store private information such as grades, login credentials, or personal data.</li>
	</ul>

Fortress sets no limits on the amount or number of files that you may store. However, there are several restrictions on the nature of files you may store:

* Many small files: Fortress is a tape archive and works best with a few, large files. Large sets of small files should be compressed into archives with utilities such as <a href="/knowledge/fortress/storage/transfer/htar">htar</a>. Other technical limitations are <a href="/knowledge/fortress/faq/about/limitations">detailed on the Fortress FAQs</a>.
* Backing up individual or departmental computers. Fortress is intended to be a research data store and not a personal or enterprise backup solution.

Additionally, while Fortress access is included with Research Computing services, storing more than 1 PB of data may incur a cost recovery charge.


### Outside Collaborators

Your Departmental Business Office can submit a Request for Privileges (R4P) to provide access to collaborators outside Purdue, including recent graduates. 

### Login & Keytabs

It is not possible to login directly to ${resource.name} via SSH or SCP.  You may access your files there efficiently using HSI, HTAR, or SFTP.  Windows Network Drive/SMB access is possible, though with significant performance loss.

A Kerberos <em>keytab</em> file is required to log into ${resource.name} via HSI or HTAR. However, <strong>all ITaP research systems may access ${resource.name} without any Kerberos keytab preparation.</strong> If for some reason you lose your keytab, you may easily regenerate one on any ITaP research system by running the command <kbd>fortresskey</kbd>.

However, to access ${resource.name} from a <strong>personal or departmental computer</strong>, you will need to first copy your Kerberos keytab file to the computer you wish to use.  This keytab can be found in your research home directory, within the hidden subdirectory named ".private" as the file "hpss.keytab" (<kbd>.private/hpss.keytab</kbd>).  This keytab will allow you to access HPSS services without needing to type a password and will remain valid for 90 days.  Your keytab on ITaP research systems will automatically be regenerated after this time, and you will need to re-copy the new keytab file to any other computers you use to directly access ${resource.name} then.
 
If you do not have an account on any ITaP research systems other than ${resource.name}, you will need to generate a keytab file using the web interface:
<ul>
 <li>
 <a href="/fortresskey/">Web Form to Generate New ${resource.name} Keytab</a><br />
 <em>Warning:  Using this invalidates your existing keytab on ITaP research systems in the <kbd>~/.private/hpss.keytab</kbd> file.</em>
 </li>
</ul>
