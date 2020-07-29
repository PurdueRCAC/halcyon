---
title: Can I download HSI or HTAR binaries for my OS platform?
tags:
 - fortress
---

### Can I download HSI or HTAR binaries for my OS platform?

Yes, visit the <a href="/downloads/archive/#hsi">Downloads</a> page to download HSI or HTAR packages for your operating system. 

<strong>Note:</strong> If your username on your desktop does not match your career account username, HSI and HTAR require configuration to connect using your career account username:
<ul>
	<li>For HSI, use the <kbd>-l careeraccount</kbd> option on the <kbd>hsi</kbd> command line.</li>
	<li>For HTAR, set the <kbd>HPSS_PRINCIPAL</kbd> environment variable to your career account username: <br/>
			bash: <kbd>export HPSS_PRINCIPAL=careeraccount</kbd><br/>
			csh/tcsh: <kbd>setenv HPSS_PRINCIPAL=careeraccount</kbd><br/>
	</li>
</ul>
