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
	public static function bootHistorable(): void
	{
		static::created(function (Model $model)
		{
			if (!method_exists($model, 'writeHistory'))
			{
				return;
			}

			$model->writeHistory('created', [], $model->toArray());
		});

		static::updated(function (Model $model)
		{
			if (!method_exists($model, 'writeHistory'))
			{
				return;
			}

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
			if (!method_exists($model, 'writeHistory'))
			{
				return;
			}

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
	public function writeHistory(string $action, array $old = [], array $new = []): void
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
	public function history(): MorphMany
	{
		return $this->morphMany(History::class, 'historable');
	}
}
