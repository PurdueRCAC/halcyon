<?php
namespace App\Halcyon\Utilities;

use Exception;

class LdapConnection
{
	/**
	 * LDAP connection parameters
	 *
	 * @var  string
	 */
	private $basedn = '';

	/**
	 * LDAP connection parameters
	 *
	 * @var  string
	 */
	private $url = '';

	/**
	 * LDAP connection parameters
	 *
	 * @var  string
	 */
	private $rdn = null;

	/**
	 * LDAP connection parameters
	 *
	 * @var  string
	 */
	private $pass = null;

	/**
	 * LDAP connection
	 *
	 * @var  object
	 */
	private $connection;

	/**
	 * Open connection to LDAP.
	 *
	 * @param   string  $basedn
	 * @param   string  $url
	 * @param   string  $pass
	 * @param   string  $rdn
	 * @return  void
	 */
	public function __construct($basedn, $url, $pass = null, $rdn = null)
	{
		$params = parse_ini_string_m(file_get_contents($file));

		if (empty($basedn) || empty($url))
		{
			throw new Exception('Invalid configuration for LDAP service.');
		}

		$this->basedn = $basedn;
		$this->url    = $url;

		if (!empty($pass))
		{
			$this->pass = $pass;
		}

		if (!empty($rdn))
		{
			$this->rdn = $rdn;
		}

		if (!function_exists('ldap_connect'))
		{
			throw new Exception('Required LDAP function not found. Please ensure the php-ldap extension is installed and configured.');
		}

		$this->connection = ldap_connect($this->url);

		if (!$this->connection)
		{
			throw new Exception('Cannot connect to ' . strtoupper($service) . ' LDAP.');
		}

		ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);

		$bind = @ldap_bind($this->connection, $this->rdn, $this->pass);

		if (!$bind)
		{
			throw new Exception('Cannot bind to LDAP.');
		}
	}

	/**
	 * Close connection to LDAP.
	 *
	 * @return  void
	 */
	public function __destruct()
	{
		if ($this->connection)
		{
			ldap_close($this->connection);
		}
	}

	/**
	 * Query LDAP and fill the passed in reference with the returned data.
	 * Return the number of rows found.
	 *
	 * @param   string   $filter
	 * @param   array    $fields
	 * @return  array
	 */
	public function query($filter, $fields = array())
	{
		$data = array();

		// Note you need to "@" this, because if your request matches too many entries the
		// I2A2 LDAP translator returns LDAP error #21, "Invalid Syntax".  Why?  Who knows!
		@$result = ldap_search($this->connection, $this->basedn, $filter, $fields);

		if ($result)
		{
			$data = ldap_get_entries($this->connection, $result);
		}

		return $data;
	}
}
