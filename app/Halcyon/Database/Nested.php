<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Database;

use Illuminate\Database\Eloquent\Model;

/**
 * Database ORM class for implementing nested set records
 */
class Nested extends Model
{
	/**
	 * Scopes to limit the realm of the nested set functions
	 *
	 * @var  array
	 **/
	protected $scopes = [];

	/**
	 * Updates all subsequent vars after new child insertion
	 *
	 * @param   string  $pos   The position being updated, whether left or right
	 * @param   int     $base  The base level after which values should be changed
	 * @param   bool    $add   Whether or not we're adding or subtracted from existing
	 * @return  $this
	 **/
	private function updateTrailing($pos = 'lft', $base = 0, $add = true)
	{
		// Reposition new values of displaced items
		$query = $this->newQuery();
		$query->table($this->getTable())
			->where($pos, '>=', $base)
			->where('id', '!=', $this->id)
			->update([
				$pos => $pos . ($add ? '+' : '-') . '2',
			]);

		return $this;
	}

	/**
	 * Resolves the trailing left and right values for the new model
	 *
	 * @param   int    $base  The base level after which values should be changed
	 * @return  $this
	 **/
	private function resolveTrailing($base, $add = true)
	{
		return $this->updateTrailing('lft', $base, $add)
		            ->updateTrailing('rgt', $base, $add);
	}

	/**
	 * Establishes the model as a proper object as needed
	 *
	 * @param   object|int  $model  The model to resolve
	 * @return  $this
	 **/
	private function establishIsModel(&$model)
	{
		// Turn model into an object if need be
		if (!is_object($model))
		{
			$model = static::findOrFail((int) $model);
		}

		return $this;
	}

	/**
	 * Sets the default scopes on the model
	 *
	 * @param   object|int  $parent  The parent of the child being created
	 * @return  $this
	 **/
	private function establishBaseParametersFromParent($parent)
	{
		$this->parent_id = $parent->id;
		$this->level = $parent->level + 1;

		foreach ($this->scopes as $scope)
		{
			$this->$scope = $parent->$scope;
		}

		return $this->applyScopes($parent);
	}

	/**
	 * Applies the scopes of the given model to the current
	 *
	 * @param   object|int  $parent  The parent from which to inherit
	 * @param   string      $method  The way in which scopes are applied
	 * @return  $this
	 **/
	private function applyScopes($parent, $method = 'set')
	{
		// Inherit scopes from parent
		foreach ($this->scopes as $scope)
		{
			$this->$method($scope, $parent->$scope);
		}

		return $this;
	}

	/**
	 * Applies the scopes of the given model to the current pending query
	 *
	 * @param   object|int  $parent  The parent from which to inherit
	 * @return  $this
	 **/
	private function applyScopesWhere($parent)
	{
		foreach ($this->scopes as $scope)
		{
			$this->where($scope, '=', $parent->$scope);
		}
		return $this;
		//return $this->applyScopes($parent, 'where');
	}

	/**
	 * Saves the current model to the database as the nth child of the given parent
	 *
	 * @param   object|int  $parent  The parent of the child being created
	 * @return  bool
	 **/
	public function saveAsChildOf($parent)
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
	public function saveAsFirstChildOf($parent)
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
	public function saveAsLastChildOf($parent)
	{
		return $this->saveAsChildOf($parent);
	}

	/**
	 * Saves a new root node element
	 *
	 * @return  bool
	 **/
	public function saveAsRoot()
	{
		// Compute the location where the item should reside
		$this->parent_id = 0;
		$this->level = 0;
		$this->lft = 0;
		$this->rgt = 1;

		// Save
		return $this->save();
	}

	/**
	 * Deletes a model, rearranging subordinate nodes as appropriate
	 *
	 * @return  bool
	 **/
	public function delete()
	{
		if (!parent::delete())
		{
			return false;
		}

		foreach ($this->getDescendants() as $descendant)
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
	 * Establishes the query for the immediate children of the current model
	 *
	 * @return  array
	 **/
	public function children()
	{
		return $this->descendants(1);
	}

	/**
	 * Grabs the immediate children of the current model
	 *
	 * @return  array
	 **/
	public function getChildren()
	{
		return $this->children()->get();
	}

	/**
	 * Establishes the query for all of the descendants of the current model
	 *
	 * @param   int  $level  The level to limit to
	 * @return  array
	 **/
	public function descendants($level = null)
	{
		$instance = self::query();
		$instance->where('level', '>', $this->level)
		         ->orderBy('lft', 'asc');

		if (isset($level))
		{
			$instance->where('level', '<=', $this->level + $level);
		}

		return $instance->where('lft', '>', $this->lft)
		                ->where('rgt', '<', $this->rgt)
		                ->applyScopesWhere($this);
	}

	/**
	 * Grabs all of the descendants of the current model
	 *
	 * @param   int  $level  The level to limit to
	 * @return  array
	 **/
	public function getDescendants($level = null)
	{
		return $this->descendants($level)->get();
	}
}
