<?php

namespace App\Modules\Orders\Entities;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Connection;
use Illuminate\Contracts\Events\Dispatcher;
use App\Modules\Orders\Exceptions\UnknownModelException;
use App\Modules\Orders\Exceptions\InvalidRowIDException;
use App\Modules\Orders\Exceptions\CartAlreadyStoredException;
use Carbon\Carbon;

/**
 * Based on Gloudemans\Shoppingcart\Cart
 */
class Cart
{
	/**
	 * @var string
	 */
	const DEFAULT_INSTANCE = 'default';

	/**
	 * Instance of the session manager.
	 *
	 * @var SessionManager
	 */
	private $session;

	/**
	 * Instance of the event dispatcher.
	 * 
	 * @var Dispatcher
	 */
	private $events;

	/**
	 * Holds the current cart instance.
	 *
	 * @var Cart
	 */
	private $instance;

	/**
	 * Cart constructor.
	 *
	 * @param SessionManager $session
	 * @param Dispatcher $events
	 * @return void
	 */
	public function __construct(SessionManager $session, Dispatcher $events)
	{
		$this->session = $session;
		$this->events = $events;

		$this->instance(self::DEFAULT_INSTANCE);
	}

	/**
	 * Set the current cart instance.
	 *
	 * @param string|null $instance
	 * @return self
	 */
	public function instance($instance = null): self
	{
		$instance = $instance ?: self::DEFAULT_INSTANCE;

		$this->instance = sprintf('%s.%s', 'cart', $instance);

		return $this;
	}

	/**
	 * Get the current cart instance.
	 *
	 * @return string
	 */
	public function currentInstance(): string
	{
		return str_replace('cart.', '', $this->instance);
	}

	/**
	 * Add an item to the cart.
	 *
	 * @param mixed     $id
	 * @param mixed     $name
	 * @param int|float $qty
	 * @param float     $price
	 * @param array     $options
	 * @return CartItem
	 */
	public function add($id, $name = null, $qty = null, $price = null, array $options = []): CartItem
	{
		if ($this->isMulti($id))
		{
			return array_map(function ($item)
			{
				return $this->add($item);
			}, $id);
		}

		$cartItem = $this->createCartItem($id, $name, $qty, $price, $options);

		$content = $this->getContent();

		if ($content->has($cartItem->rowId))
		{
			$cartItem->qty += $content->get($cartItem->rowId)->qty;
		}

		$content->put($cartItem->rowId, $cartItem);

		$this->events->dispatch('cart.added', $cartItem);

		$this->session->put($this->instance, $content);

		return $cartItem;
	}

	/**
	 * Update the cart item with the given rowId.
	 *
	 * @param string $rowId
	 * @param mixed  $qty
	 * @return CartItem|null
	 */
	public function update($rowId, $qty)
	{
		$cartItem = $this->get($rowId);

		/*if ($qty instanceof Buyable)
		{
			$cartItem->updateFromBuyable($qty);
		}
		else*/
		if (is_array($qty))
		{
			$cartItem->updateFromArray($qty);
		}
		else
		{
			$cartItem->qty = $qty;
		}

		$content = $this->getContent();

		if ($rowId !== $cartItem->rowId)
		{
			$content->pull($rowId);

			if ($content->has($cartItem->rowId))
			{
				$existingCartItem = $this->get($cartItem->rowId);
				$cartItem->setQuantity($existingCartItem->qty + $cartItem->qty);
			}
		}

		if ($cartItem->qty <= 0)
		{
			$this->remove($cartItem->rowId);
			return;
		}
		else
		{
			$content->put($cartItem->rowId, $cartItem);
		}

		$this->events->dispatch('cart.updated', $cartItem);

		$this->session->put($this->instance, $content);

		return $cartItem;
	}

	/**
	 * Remove the cart item with the given rowId from the cart.
	 *
	 * @param string $rowId
	 * @return void
	 */
	public function remove($rowId): void
	{
		$cartItem = $this->get($rowId);

		$content = $this->getContent();

		$content->pull($cartItem->rowId);

		$this->events->dispatch('cart.removed', $cartItem);

		$this->session->put($this->instance, $content);
	}

	/**
	 * Get a cart item from the cart by its rowId.
	 *
	 * @param string $rowId
	 * @return CartItem
	 * @throws InvalidRowIDException
	 */
	public function get($rowId)
	{
		$content = $this->getContent();

		if (!$content->has($rowId))
		{
			throw new InvalidRowIDException("The cart does not contain rowId {$rowId}.");
		}

		return $content->get($rowId);
	}

	/**
	 * Destroy the current cart instance.
	 *
	 * @return void
	 */
	public function destroy(): void
	{
		$this->session->remove($this->instance);
	}

	/**
	 * Get the content of the cart.
	 *
	 * @return Collection
	 */
	public function content()
	{
		if (is_null($this->session->get($this->instance)))
		{
			return new Collection([]);
		}

		return $this->session->get($this->instance);
	}

	/**
	 * Get the number of items in the cart.
	 *
	 * @return int|float
	 */
	public function count()
	{
		$content = $this->getContent();

		return $content->sum('qty');
	}

	/**
	 * Get the total price of the items in the cart.
	 *
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return string
	 */
	public function total($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
	{
		$content = $this->getContent();

		$total = $content->reduce(function ($total, CartItem $cartItem)
		{
			return $total + ($cartItem->qty * $cartItem->priceTax);
		}, 0);

		return $this->numberFormat($total, $decimals, $decimalPoint, $thousandSeperator);
	}

	/**
	 * Get the total tax of the items in the cart.
	 *
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return float
	 */
	public function tax($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
	{
		$content = $this->getContent();

		$tax = $content->reduce(function ($tax, CartItem $cartItem)
		{
			return $tax + ($cartItem->qty * $cartItem->tax);
		}, 0);

		return $this->numberFormat($tax, $decimals, $decimalPoint, $thousandSeperator);
	}

	/**
	 * Get the subtotal (total - tax) of the items in the cart.
	 *
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return float
	 */
	public function subtotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
	{
		$content = $this->getContent();

		$subTotal = $content->reduce(function ($subTotal, CartItem $cartItem)
		{
			return $subTotal + ($cartItem->qty * $cartItem->price);
		}, 0);

		return $this->numberFormat($subTotal, $decimals, $decimalPoint, $thousandSeperator);
	}

	/**
	 * Search the cart content for a cart item matching the given search closure.
	 *
	 * @param \Closure $search
	 * @return Collection
	 */
	public function search(Closure $search)
	{
		$content = $this->getContent();

		return $content->filter($search);
	}

	/**
	 * Associate the cart item with the given rowId with the given model.
	 *
	 * @param string $rowId
	 * @param mixed  $model
	 * @return void
	 * @throws UnknownModelException
	 */
	public function associate($rowId, $model)
	{
		if (is_string($model) && !class_exists($model))
		{
			throw new UnknownModelException("The supplied model {$model} does not exist.");
		}

		$cartItem = $this->get($rowId);

		$cartItem->associate($model);

		$content = $this->getContent();

		$content->put($cartItem->rowId, $cartItem);

		$this->session->put($this->instance, $content);
	}

	/**
	 * Set the tax rate for the cart item with the given rowId.
	 *
	 * @param string    $rowId
	 * @param int|float $taxRate
	 * @return void
	 */
	public function setTax($rowId, $taxRate): void
	{
		$cartItem = $this->get($rowId);

		$cartItem->setTaxRate($taxRate);

		$content = $this->getContent();

		$content->put($cartItem->rowId, $cartItem);

		$this->session->put($this->instance, $content);
	}

	/**
	 * Store the current instance of the cart.
	 *
	 * @param mixed $identifier
	 * @return void
	 */
	public function store($identifier): void
	{
		$content = $this->getContent();

		if ($this->storedCartWithIdentifierExists($identifier))
		{
			$this->getConnection()->table($this->getTableName())
				->where('identifier', '=', $identifier)
				->where('instance', '=', $this->currentInstance())
				->update([
					'content' => serialize($content),
					'updated_at' => Carbon::now()->toDateTimeString()
				]);
		}
		else
		{
			$this->getConnection()->table($this->getTableName())
				->insert([
					'identifier' => $identifier,
					'instance' => $this->currentInstance(),
					'content' => serialize($content),
					'created_at' => Carbon::now()->toDateTimeString()
				]);
		}

		$this->events->dispatch('cart.stored');
	}

	/**
	 * Restore the cart with the given identifier.
	 *
	 * @param mixed $identifier
	 * @return void
	 */
	public function restore($identifier): void
	{
		$this->session->forget('cart');

		if (!$this->storedCartWithIdentifierExists($identifier))
		{
			return;
		}

		$stored = $this->getConnection()->table($this->getTableName())
			->where('identifier', $identifier)
			->first();

		$storedContent = unserialize($stored->content);

		$currentInstance = $this->currentInstance();

		$this->instance($stored->instance);

		//$this->session->forget('cart');

		$content = $this->getContent();

		foreach ($storedContent as $cartItem)
		{
			$content->put($cartItem->rowId, $cartItem);
		}

		$this->events->dispatch('cart.restored');

		$this->session->put($this->instance, $content);

		$this->instance($currentInstance);

		/*$this->getConnection()->table($this->getTableName())
			->where('identifier', $identifier)
			->delete();*/
	}

	/**
	 * Restore the cart with the given identifier.
	 *
	 * @param mixed $identifier
	 * @param bool  $sessionOnly
	 * @return void
	 */
	public function forget($identifier, $sessionOnly = false): void
	{
		$this->session->forget('cart');

		if (!$sessionOnly)
		{
			if (!$this->storedCartWithIdentifierExists($identifier))
			{
				return;
			}

			$this->getConnection()->table($this->getTableName())
				->where('identifier', $identifier)
				->delete();

			$this->events->dispatch('cart.emptied');
		}
	}

	/**
	 * Magic method to make accessing the total, tax and subtotal properties possible.
	 *
	 * @param string $attribute
	 * @return float|null
	 */
	public function __get($attribute)
	{
		if ($attribute === 'total')
		{
			return $this->total();
		}

		if ($attribute === 'tax')
		{
			return $this->tax();
		}

		if ($attribute === 'subtotal')
		{
			return $this->subtotal();
		}

		return null;
	}

	/**
	 * Get the carts content, if there is no cart content set yet, return a new empty Collection
	 *
	 * @return Collection
	 */
	protected function getContent()
	{
		$content = $this->session->has($this->instance)
			? $this->session->get($this->instance)
			: new Collection;

		return $content;
	}

	/**
	 * Create a new CartItem from the supplied attributes.
	 *
	 * @param mixed     $id
	 * @param mixed     $name
	 * @param int|float $qty
	 * @param float     $price
	 * @param array     $options
	 * @return CartItem
	 */
	private function createCartItem($id, $name, $qty, $price, array $options): CartItem
	{
		if ($id instanceof Buyable)
		{
			$cartItem = CartItem::fromBuyable($id, $qty ?: []);
			$cartItem->setQuantity($name ?: 1);
			$cartItem->associate($id);
		}
		elseif (is_array($id))
		{
			$cartItem = CartItem::fromArray($id);
			$cartItem->setQuantity($id['qty']);
		}
		else
		{
			$cartItem = CartItem::fromAttributes($id, $name, $price, $options);
			$cartItem->setQuantity($qty);
		}

		$cartItem->setTaxRate(config('cart.tax', 0));

		return $cartItem;
	}

	/**
	 * Check if the item is a multidimensional array or an array of Buyables.
	 *
	 * @param mixed $item
	 * @return bool
	 */
	private function isMulti($item): bool
	{
		if (!is_array($item))
		{
			return false;
		}

		return is_array(head($item)) || head($item) instanceof Buyable;
	}

	/**
	 * @param mixed $identifier
	 * @return bool
	 */
	private function storedCartWithIdentifierExists($identifier): bool
	{
		return $this->getConnection()
			->table($this->getTableName())
			->where('identifier', $identifier)
			->exists();
	}

	/**
	 * Get the database connection.
	 *
	 * @return Connection
	 */
	private function getConnection()
	{
		$connectionName = $this->getConnectionName();

		return app(DatabaseManager::class)->connection($connectionName);
	}

	/**
	 * Get the database table name.
	 *
	 * @return string
	 */
	private function getTableName()
	{
		return 'ordercarts';
	}

	/**
	 * Get the database connection name.
	 *
	 * @return string
	 */
	private function getConnectionName(): string
	{
		$connection = config('module.orders.database.connection');

		return is_null($connection) ? config('database.default') : $connection;
	}

	/**
	 * Get the Formated number
	 *
	 * @param mixed $value
	 * @param string $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return string
	 */
	private function numberFormat($value, $decimals, $decimalPoint, $thousandSeperator)
	{
		if (is_null($decimals))
		{
			$decimals = is_null(config('module.orders.format.decimals')) ? 2 : config('module.orders.format.decimals');
		}
		if (is_null($decimalPoint))
		{
			$decimalPoint = is_null(config('module.orders.format.decimal_point')) ? '.' : config('module.orders.format.decimal_point');
		}
		if (is_null($thousandSeperator))
		{
			$thousandSeperator = is_null(config('module.orders.format.thousand_seperator')) ? ',' : config('module.orders.format.thousand_seperator');
		}

		return number_format($value, $decimals, $decimalPoint, $thousandSeperator);
	}
}
