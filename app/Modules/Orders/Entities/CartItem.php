<?php

namespace App\Modules\Orders\Entities;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use InvalidArgumentException;

/**
 * Based on Gloudemans\Shoppingcart\CartItem
 */
class CartItem implements Arrayable, Jsonable
{
	/**
	 * The rowID of the cart item.
	 *
	 * @var string
	 */
	public $rowId;

	/**
	 * The ID of the cart item.
	 *
	 * @var int|string
	 */
	public $id;

	/**
	 * The quantity for this cart item.
	 *
	 * @var int|float
	 */
	public $qty;

	/**
	 * The name of the cart item.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The price without TAX of the cart item.
	 *
	 * @var float
	 */
	public $price;

	/**
	 * The options for this cart item.
	 *
	 * @var array
	 */
	public $options;

	/**
	 * The FQN of the associated model.
	 *
	 * @var string|null
	 */
	private $associatedModel = null;

	/**
	 * The tax rate for the cart item.
	 *
	 * @var int|float
	 */
	private $taxRate = 0;

	/**
	 * CartItem constructor.
	 *
	 * @param int|string $id
	 * @param string     $name
	 * @param float      $price
	 * @param array      $options
	 * @throws InvalidArgumentException
	 */
	public function __construct($id, $name, $price, array $options = [])
	{
		if (empty($id))
		{
			throw new InvalidArgumentException('Please supply a valid identifier.');
		}
		if (empty($name))
		{
			throw new InvalidArgumentException('Please supply a valid name.');
		}
		if (strlen($price) < 0 || ! is_numeric($price))
		{
			throw new InvalidArgumentException('Please supply a valid price.');
		}

		$this->id       = $id;
		$this->name     = $name;
		$this->price    = floatval($price);
		$this->options  = $options;
		$this->rowId    = $this->generateRowId($id, $options);
	}

	/**
	 * Returns the formatted price without TAX.
	 *
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return string
	 */
	public function price($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
	{
		return $this->numberFormat($this->price, $decimals, $decimalPoint, $thousandSeperator);
	}
	
	/**
	 * Returns the formatted price with TAX.
	 *
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return string
	 */
	public function priceTax($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
	{
		return $this->numberFormat($this->priceTax, $decimals, $decimalPoint, $thousandSeperator);
	}

	/**
	 * Returns the formatted subtotal.
	 * Subtotal is price for whole CartItem without TAX
	 *
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return string
	 */
	public function subtotal($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
	{
		return $this->numberFormat($this->subtotal, $decimals, $decimalPoint, $thousandSeperator);
	}
	
	/**
	 * Returns the formatted total.
	 * Total is price for whole CartItem with TAX
	 *
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return string
	 */
	public function total($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
	{
		return $this->numberFormat($this->total, $decimals, $decimalPoint, $thousandSeperator);
	}

	/**
	 * Returns the formatted tax.
	 *
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return string
	 */
	public function tax($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
	{
		return $this->numberFormat($this->tax, $decimals, $decimalPoint, $thousandSeperator);
	}
	
	/**
	 * Returns the formatted tax.
	 *
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return string
	 */
	public function taxTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null): string
	{
		return $this->numberFormat($this->taxTotal, $decimals, $decimalPoint, $thousandSeperator);
	}

	/**
	 * Set the quantity for this cart item.
	 *
	 * @param int|float $qty
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function setQuantity($qty): void
	{
		if (empty($qty) || ! is_numeric($qty))
		{
			throw new InvalidArgumentException('Please supply a valid quantity.');
		}

		$this->qty = $qty;
	}

	/**
	 * Update the cart item from an array.
	 *
	 * @param array $attributes
	 * @return void
	 */
	public function updateFromArray(array $attributes): void
	{
		$this->id       = array_get($attributes, 'id', $this->id);
		$this->qty      = array_get($attributes, 'qty', $this->qty);
		$this->name     = array_get($attributes, 'name', $this->name);
		$this->price    = array_get($attributes, 'price', $this->price);
		$this->priceTax = $this->price + $this->tax;
		$this->options  = array_get($attributes, 'options', $this->options);

		$this->rowId = $this->generateRowId($this->id, $this->options->all());
	}

	/**
	 * Associate the cart item with the given model.
	 *
	 * @param mixed $model
	 * @return self
	 */
	public function associate($model): self
	{
		$this->associatedModel = is_string($model) ? $model : get_class($model);
		
		return $this;
	}

	/**
	 * Set the tax rate.
	 *
	 * @param int|float $taxRate
	 * @return self
	 */
	public function setTaxRate($taxRate): self
	{
		$this->taxRate = $taxRate;
		
		return $this;
	}

	/**
	 * Get an attribute from the cart item or get the associated model.
	 *
	 * @param string $attribute
	 * @return mixed
	 */
	public function __get($attribute)
	{
		if (property_exists($this, $attribute))
		{
			return $this->{$attribute};
		}

		if ($attribute === 'priceTax') {
			return $this->price + $this->tax;
		}

		if ($attribute === 'subtotal')
		{
			return $this->qty * $this->price;
		}

		if ($attribute === 'total')
		{
			return $this->qty * ($this->priceTax);
		}

		if ($attribute === 'tax')
		{
			return $this->price * ($this->taxRate / 100);
		}

		if ($attribute === 'taxTotal')
		{
			return $this->tax * $this->qty;
		}

		if ($attribute === 'model' && isset($this->associatedModel))
		{
			return with(new $this->associatedModel)->find($this->id);
		}

		return null;
	}

	/**
	 * Create a new instance from the given array.
	 *
	 * @param array $attributes
	 * @return self
	 */
	public static function fromArray(array $attributes): self
	{
		$options = array_get($attributes, 'options', []);

		return new self($attributes['id'], $attributes['name'], $attributes['price'], $options);
	}

	/**
	 * Create a new instance from the given attributes.
	 *
	 * @param int|string $id
	 * @param string     $name
	 * @param float      $price
	 * @param array      $options
	 * @return self
	 */
	public static function fromAttributes($id, $name, $price, array $options = []): self
	{
		return new self($id, $name, $price, $options);
	}

	/**
	 * Generate a unique id for the cart item.
	 *
	 * @param string $id
	 * @param array  $options
	 * @return string
	 */
	protected function generateRowId($id, array $options): string
	{
		ksort($options);

		return md5($id . serialize($options));
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array<string,mixed>
	 */
	public function toArray(): array
	{
		return [
			'rowId'    => $this->rowId,
			'id'       => $this->id,
			'name'     => $this->name,
			'qty'      => $this->qty,
			'price'    => $this->price(),
			'options'  => $this->options, //->toArray(),
			'tax'      => $this->tax(),
			'subtotal' => $this->subtotal()
		];
	}

	/**
	 * Convert the object to its JSON representation.
	 *
	 * @param int $options
	 * @return string
	 */
	public function toJson($options = 0): string
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Get the formatted number.
	 *
	 * @param float  $value
	 * @param int    $decimals
	 * @param string $decimalPoint
	 * @param string $thousandSeperator
	 * @return string
	 */
	private function numberFormat($value, $decimals, $decimalPoint, $thousandSeperator): string
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
