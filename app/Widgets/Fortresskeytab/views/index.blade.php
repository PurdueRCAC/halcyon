
<?php
// If the user is from an off-campus IP, explain they must use VPN.
if ($offcampus):
	?>
	<div>
		<p>
			This page will allow you to create and download a new Kerberos keytab file which will allow you access to the Fortress HPSS archive system.
		</p>
		<p>
			<strong>You currently appear to be using a connection away from the Purdue University campus.  Fortress requires that you either be using an on-campus connection or first connect to the Purdue VPN.</strong>
		</p>
		<p>
			Please <a href="http://www.itap.purdue.edu/connections/vpn/" target="_blank" rel="noopener">connect to the Purdue VPN</a> now, and then reload this page.
		</p>
	</div>
	<?php
else:
	// If the user clicked the button, try to create them a new keytab.
	?>
	<div>
		<form action="https://tools.itap.purdue.edu/bin/krbinit.cgi" method="post">
			<p>
				This page will allow you to create and download a new Kerberos keytab file which will allow you access to the Fortress HPSS archive system from systems outside of ITaP Research Computing through HSI and HTAR. 
			</p>
			<p>
				If you are using HSI and HTAR on ITaP Research Computing systems, the keytab file is automatically managed for you. Downloading a keytab file through this page is only necessary if you wish to use HSI and HTAR on a personal or departmental system.
			</p>
			<p>
				This keytab file is an access token to all your Fortress data, and must be protected.  Always make sure permissions on this keytab are set to allow access by you alone, and never share this keytab with other users.
			</p>
			<p>
				<strong>Warning: Creating a keytab here immediately invalidates any existing keytab for Fortress you may have.</strong>
			</p>
			<p>
				Once you download your keytab, you must place this in your home directory under the ".private" subdirectory, and you must restrict the permissions on this file to just yourself.  If you are using a Linux, Unix, or Mac computer, you may restrict the permissions with the following command:
			</p>
			<pre>$ chmod g=,o= ~/.private/hpss.keytab</pre>

			<input type="submit" id="generate" name="doit" class="btn btn-primary" value="Create a New HPSS Keytab" />
		</form>
	</div>
	<?php
endif;
