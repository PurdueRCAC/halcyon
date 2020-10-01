<?php

namespace App\Modules\History\Traits;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Models\Log;

trait Loggable
{
	/**
	 * boot method
	 *
	 * @return  null
	 */
	protected function log($app, $method = 'GET', $status = 200, $payload = array(), $uri = '')
	{
		$method = strtoupper($method);

		if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'HEAD', 'DELETE']))
		{
			$method = 'GET';
		}

		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => (int)$status,
			'transportmethod' => $method,
			'servername'      => request()->getHttpHost(),
			'uri'             => $uri,
			'app'             => $app,
			'payload'         => json_encode($payload),
		]);
	}
}
