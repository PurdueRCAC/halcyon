<?php

namespace App\Halcyon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Database ORM class for implementing nested set records
 *
 * @property int $lft
 * @property int $rgt
 * @property int $level
 * @property int $parent_id
 */
class Nested extends Model
{
	/**
	 * Updates all subsequent vars after new child insertion
	 *
	 * @param   string  $pos   The position being updated, whether left or right
	 * @param   int     $base  The base level after which values should be changed
	 * @param   bool    $add   Whether or not we're adding or subtracted from existing
	 * @return  self
	 **/
	private function updateTrailing($pos = 'lft', $base = 0, $add = true): self
	{
		// Reposition new values of displaced items
		$query = self::query()
			->where($pos, '>=', $base)
			->where('id', '!=', $this->id)
			->update([
				$pos => DB::raw($pos . ($add ? '+' : '-') . '2'),
			]);

		return $this;
	}

	/**
	 * Resolves the trailing left and right values for the new model
	 *
	 * @param   int   $base  The base level after which values should be changed
	 * @param   bool  $add
	 * @return  self
	 **/
	private function resolveTrailing($base, $add = true): self
	{
		return $this->updateTrailing('lft', $base, $add)
		            ->updateTrailing('rgt', $base, $add);
	}

	/**
	 * Establishes the model as a proper object as needed
	 *
	 * @param   object|int  $model  The model to resolve
	 * @return  self
	 **/
	private function establishIsModel(&$model): self
	{
		// Turn model into an object if need be
		if (!is_object($model))
		{
			$model = static::findOrFail((int) $model);
		}

		return $this;
	}

	/**
	 * Inherit some data from parent
	 *
	 * @param   object  $parent  The parent of the child being created
	 * @return  self
	 **/
	private function establishBaseParametersFromParent($parent): self
	{
		$this->parent_id = $parent->id;
		$this->level = $parent->level + 1;

		return $this;
	}

	/**
	 * Saves the current model to the database as the nth child of the given parent
	 *
	 * @param   object|int  $parent  The parent of the child being created
	 * @return  bool
	 **/
	public function saveAsChildOf($parent): bool
	{
		$this->establishIsModel($parent)
		     ->establishBaseParametersFromParent($parent);

		// Compute the location where the item should reside
		$this->lft = $parent->rgt;
		$this->rgt = $parent->rgt + 1;

		// Save
		if (!$this->save())
		{
			return false;
		}

		// Reposition new values of displaced items
		$this->resolveTrailing($parent->rgt);

		return true;
	}

	/**
	 * Saves the current model to the database as the first child of the given parent
	 *
	 * @param   object|int  $parent  The parent of the child being created
	 * @return  bool
	 **/
	public function saveAsFirstChildOf($parent): bool
	{
		$this->establishIsModel($parent)
		     ->establishBaseParametersFromParent($parent);

		// Compute the location where the item should reside
		$this->lft = $parent->lft + 1;
		$this->rgt = $parent->lft + 2;

		// Save
		if (!$this->save())
		{
			return false;
		}

		// Reposition new values of displaced items
		$this->resolveTrailing($parent->lft + 1);

		return true;
	}

	/**
	 * Saves the current model to the database as the last child of the given parent
	 *
	 * @param   object|int  $parent  The parent of the child being created
	 * @return  bool
	 **/
	public function saveAsLastChildOf($parent): bool
	{
		return $this->saveAsChildOf($parent);
	}

	/**
	 * Saves a new root node element
	 *
	 * @return  bool
	 **/
	public function saveAsRoot(): bool
	{
		$this->parent_id = 0;
		$this->level = 0;
		$this->lft = 0;
		$this->rgt = 1;

		return $this->save();
	}

	/**
	 * Deletes a model, rearranging subordinate nodes as appropriate
	 *
	 * @return  bool
	 **/
	public function delete(): bool
	{
		if (!parent::delete())
		{
			return false;
		}

		foreach ($this->descendants as $descendant)
		{
			$descendant->delete();

			// We have to decrement our internal reference to right here
			// so that we ultimately resolve trailing below based on the
			// properly updated value, otherwise anything upstream of
			// what we're destroying won't be properly updated
			$this->rgt -= 2;
		}

		// Reposition new values of displaced items
		$this->resolveTrailing($this->rgt, false);

		return true;
	}

	/**
	 * Defines a relationship to a parent page
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parent_id');
	}

	/**
	 * Defines a relationship to child pages
	 *
	 * @return  HasMany
	 */
	public function children(): HasMany
	{
		return $this->hasMany(self::class, 'parent_id');
	}

	/**
	 * Get all parents
	 *
	 * @param   array<int,self>  $ancestors
	 * @return  array<int,self>
	 */
	public function ancestors(array $ancestors = []): array
	{
		$parent = $this->parent;

		if ($parent && $parent->level > 0)
		{
			$ancestors[] = $parent;

			if ($parent->parent_id)
			{
				$ancestors = $parent->ancestors($ancestors);
			}
		}

		return $ancestors;
	}

	/**
	 * Get all descendants
	 *
	 * @return  Builder
	 */
	public function descendants(): Builder
	{
		return self::query()
			->where('lft', '>', $this->lft)
			->where('rgt', '<', $this->rgt)
			->orderBy('lft', 'asc');
	}
}
