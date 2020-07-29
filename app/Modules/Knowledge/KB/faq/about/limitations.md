---
title: What limitiations does Fortress have?
tags:
 - fortress
---

### What limitiations does Fortress have?

Fortress has a few limitations that you should keep in mind:
<ul>
	<li>Fortress does not support direct FTP or SCP transfers. SFTP connections are supported.</li>
	<li>Fortress does not support Unicode filenames.  All filenames must contain only ASCII characters.</li>
	<li>Fortress does not support sparse files.</li>
	<li id="nosmallfiles">Fortress is a tape archive. While it can handle use case of <em>"multitude of small files"</em>, performance may be severely decreased (compared to a much preferred case of <em>"fewer files of much larger size"</em>). If you need to store a large number of small files, we strongly recommend that you bundle them up first (with <kbd>zip</kbd>, <kbd>tar</kbd>, <kbd>htar</kbd>, etc) before placing resulting archive into Fortress.  Note: a <em>"small file"</em> on Fortress scale is typically considered something under 30-50MB per file.</li>
	<li>HTAR has an individual file size limit of 64GB. If any files you are trying to archive with HTAR are greater than 64GB, then HTAR will immediately fail. This does not limit the number of files in the archive or the total overall size of the archive. To get around this limitation, try using the <kbd>htar_large</kbd> command. It is slower than using HTAR but it will work around the 64GB file size limit.</li>
</ul>
