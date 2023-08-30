<?php
namespace App\Http\Pathway;

/**
 * Pathway trail class
 */
class Trail implements \Iterator, \ArrayAccess, \Countable
{
	/**
	 * Container for items
	 *
	 * @var  array<int,Item>
	 */
	private $items = array();

	/**
	 * Create and add an item to the pathway.
	 *
	 * @param   string  $name  The name of the item.
	 * @param   string  $link  The link to the item.
	 * @return  self
	 */
	public function append($name, $link = ''): self
	{
		$this->items[] = new Item($name, $link);

		return $this;
	}

	/**
	 * Create and prepend an item to the pathway.
	 *
	 * @param   string  $name  The name of the item.
	 * @param   string  $link  The link to the item.
	 * @return  self
	 */
	public function prepend($name, $link = ''): self
	{
		$b = new Item($name, $link);
		array_unshift($this->items, $b);

		return $this;
	}

	/**
	 * Create and return an array of the crumb names.
	 *
	 * @return  array<int,string>
	 */
	public function names(): array
	{
		$names = array();

		foreach ($this->items as $item)
		{
			$names[] = $item->name;
		}

		return array_values($names);
	}

	/**
	 * Return the list of crumbs
	 *
	 * @return  array<int,Item>
	 */
	public function all(): array
	{
		return $this->items;
	}

	/**
	 * Set an item in the list
	 *
	 * @param   int  $offset
	 * @param   Item   $value
	 * @return  void
	 */
	public function set($offset, Item $value): void
	{
		$this->offsetSet($offset, $value);
	}

	/**
	 * Get an item from the list
	 *
	 * @param   int  $offset
	 * @return  Item|null
	 */
	public function get($offset): ?Item
	{
		return $this->offsetGet($offset);
	}

	/**
	 * Check if an item exists
	 *
	 * @param   int  $offset
	 * @return  bool
	 */
	public function has($offset): bool
	{
		return $this->offsetExists($offset);
	}

	/**
	 * Unset an item
	 *
	 * @param   int  $offset
	 * @return  void
	 */
	public function forget($offset): void
	{
		$this->offsetUnset($offset);
	}

	/**
	 * Clear out the list of items
	 *
	 * @return  self
	 */
	public function clear(): self
	{
		$this->items = array();

		return $this;
	}

	/**
	 * Rewind position
	 *
	 * @return  void
	 */
	public function rewind(): void
	{
		reset($this->items);
	}

	/**
	 * Return current item
	 *
	 * @return  mixed
	 */
	public function current(): mixed
	{
		return current($this->items);
	}

	/**
	 * Return position key
	 *
	 * @return  mixed
	 */
	public function key(): mixed
	{
		return key($this->items);
	}

	/**
	 * Return next item
	 *
	 * @return  void
	 */
	public function next(): void
	{
		next($this->items);
	}

	/**
	 * Is current position valid?
	 *
	 * @return  bool
	 */
	public function valid(): bool
	{
		return key($this->items) !== null;
	}

	/**
	 * Check if an item exists
	 *
	 * @param   mixed  $offset
	 * @return  bool
	 */
	public function offsetExists(mixed $offset): bool
	{
		return isset($this->items[$offset]);
	}

	/**
	 * Set an item in the list
	 *
	 * @param   mixed  $offset
	 * @param   mixed  $value
	 * @return  void
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->items[$offset] = $value;
	}

	/**
	 * Get an item from the list
	 *
	 * @param   mixed $offset
	 * @return  mixed
	 */
	public function offsetGet(mixed $offset): mixed
	{
		return isset($this->items[$offset]) ? $this->items[$offset] : null;
	}

	/**
	 * Unset an item
	 *
	 * @param   mixed $offset
	 * @return  void
	 */
	public function offsetUnset(mixed $offset): void
	{
		unset($this->items[$offset]);
	}

	/**
	 * Return a count of the number of items
	 *
	 * @return  int
	 */
	public function count(): int
	{
		return count($this->items);
	}
}
