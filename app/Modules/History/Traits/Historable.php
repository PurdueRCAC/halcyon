<?php

namespace App\Modules\History\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Models\History;

trait Historable
{
	/**
	 * boot method
	 *
	 * @return  void
	 */
	public static function bootHistorable()
	{
		static::created(function (Model $model)
		{
			$model->writeHistory('created', [], $model->toArray());
		});

		static::updated(function (Model $model)
		{
			$action = 'updated';

			$new = [];
			$old = [];
			foreach ($model->attributes as $key => $value)
			{
				$originalValue = isset($model->original[$key]) ? $model->original[$key] : null;

				if ($value != $originalValue)
				{
					$new[$key] = $value;
					$old[$key] = $originalValue;
				}
			}

			$model->writeHistory($action, $old, $new);
		});

		static::deleted(function (Model $model)
		{
			$model->writeHistory('deleted', $model->toArray());
		});
	}

	/**
	 * Write history
	 *
	 * @param   string  $action
	 * @param   array<string,mixed>   $old
	 * @param   array<string,mixed>   $new
	 * @return  void
	 */
	public function writeHistory($action, array $old = [], array $new = [])
	{
		$data = [];
		$data['historable_id'] = $this->getKey();
		$data['historable_type'] = get_class($this);
		$data['historable_table'] = $this->getTable();
		$data['user_id'] = auth()->id();
		$data['action'] = $action;
		$data['old'] = $old;
		$data['new'] = $new;

		$foo = History::create($data);
	}

	/**
	 * Model has history
	 *
	 * @return  MorphMany
	 */
	public function history()
	{
		return $this->morphMany(History::class, 'historable');
	}
}
