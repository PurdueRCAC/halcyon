<?php

namespace App\Halcyon\Utility;

/**
 * Cookie utility class
 *
 * Set and retrieve cookies in consistent manner
 */
class Cookie
{
	/**
	 * Drop a cookie
	 *
	 * @param  string $namespace  make sure the cookie name is unique
	 * @param  string $lifetime   how long the cookie should last
	 * @param  array  $data       data to be saved in cookie
	 * @return void
	 **/
	public static function bake($namespace, $lifetime, $data=array())
	{
		$hash   = \App::hash(\App::get('client')->name . ':' . $namespace);

		$key = \App::hash('');
		$crypt = new \App\Halcyon\Encryption\Encrypter(
			new \App\Halcyon\Encryption\Cipher\Simple,
			new \App\Halcyon\Encryption\Key('simple', $key, $key)
		);
		$cookie = $crypt->encrypt(serialize($data));

		// Determine whether cookie should be 'secure' or not
		$secure   = false;
		$forceSsl =config('force_ssl', false);

		if (\App::get('isAdmin') && $forceSsl >= 1)
		{
			$secure = true;
		}
		else if (!\App::get('isAdmin') && $forceSsl == 2)
		{
			$secure = true;
		}

		// Set the actual cookie
		setcookie($hash, $cookie, $lifetime, '/', '', $secure, true);
	}

	/**
	 * Retrieve a cookie
	 *
	 * @param  (string) $namespace - make sure the cookie name is unique
	 * @return (object) $cookie data
	 **/
	public static function eat($namespace)
	{
		$hash  = \App::hash(\App::get('client')->name . ':' . $namespace);

		$key = \App::hash('');
		$crypt = new \App\Halcyon\Encryption\Encrypter(
			new \App\Halcyon\Encryption\Cipher\Simple,
			new \App\Halcyon\Encryption\Key('simple', $key, $key)
		);

		if ($str = \App::get('request')->getString($hash, '', 'cookie'))
		{
			$sstr   = $crypt->decrypt($str);
			$cookie = @unserialize($sstr);

			return (object)$cookie;
		}

		return false;
	}
}
