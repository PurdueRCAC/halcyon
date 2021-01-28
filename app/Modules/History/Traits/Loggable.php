<?php

namespace App\Modules\History\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;

trait Loggable
{
	/**
	 * boot method
	 *
	 * @param   string  $app
	 * @param   string  $func
	 * @param   string  $method
	 * @param   integer $status
	 * @param   mixed   $payload
	 * @param   string  $uri
	 * @param   integer $targetuserid
	 * @return  null
	 */
	protected function log($app, $func, $method = 'GET', $status = 200, $payload = array(), $uri = '', $targetuserid = 0)
	{
		$method = strtoupper($method);

		if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'HEAD', 'DELETE']))
		{
			$method = 'GET';
		}

		$cls = $func;
		$fnc = '';
		if (strstr($func, '::'))
		{
			$func = explode('::', $func);
			$cls = array_shift($func);
			$fnc = array_pop($func);
		}

		$cls = explode('\\', $cls);
		$cls = end($cls);

		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => (int)$status,
			'transportmethod' => $method,
			'servername'      => request()->getHttpHost(),
			'uri'             => $uri,
			'app'             => $app,
			'payload'         => Str::limit(json_encode($payload), 2000, ''),
			'classname'       => Str::limit($cls, 32, ''),
			'classmethod'     => Str::limit($fnc, 16, ''),
			'targetuserid'    => $targetuserid,
		]);
	}
}
