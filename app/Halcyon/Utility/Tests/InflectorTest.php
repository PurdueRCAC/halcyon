<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Base\Tests;

use App\Halcyon\Test\Basic;
use App\Halcyon\Utility\Inflector;

/**
 * Inflector utility test
 */
class InflectorTest extends Basic
{
	// Words that should not be inflected.
	protected static $uncountable_words = array(
		'equipment',
		'information',
		'rice',
		'money',
		'species',
		'series',
		'fish',
		'meta',
		'metadata',
		'buffalo',
		'elk',
		'rhinoceros',
		'salmon',
		'bison',
		'headquarters'
	);

	protected static $strings = array(
		// -en
		'ox' => 'oxen',
		// -ices
		'mouse' => 'mice',
		'louse' => 'lice',
		// -es
		'search' => 'searches',
		'switch' => 'switches',
		'fix' => 'fixes',
		'box' => 'boxes',
		'process' => 'processes',
		// -ies
		'query' => 'queries',
		'ability' => 'abilities',
		'agency' => 'agencies',
		// -s
		'hive' => 'hives',
		'archive' => 'archives',
		// -ves
		'half' => 'halves',
		'safe' => 'saves',
		'wife' => 'wives',
		// -ses
		'basis' => 'bases',
		'diagnosis' => 'diagnoses',
		// -a
		'datum' => 'data',
		'medium' => 'media',
		// -eople
		'person' => 'people',
		'salesperson' => 'salespeople',
		// -en
		'man' => 'men',
		'woman' => 'women',
		'spokesman' => 'spokesmen',
		// hildren
		'child' => 'children',
		// -oes
		//'buffalo' => 'buffaloes',
		'tomato' => 'tomatoes',
		// -ses
		'bus' => 'buses',
		'campus' => 'campuses',
		// -es
		'alias' => 'aliases',
		'status' => 'statuses',
		'virus' => 'viruses',
		// -i
		'octopus' => 'octopi',
		// -es
		'axis' => 'axes',
		'crisis' => 'crises',
		'testis' => 'testes',
		// -s
		'cat' => 'cats',
		'dog' => 'dogs',
		'cup' => 'cups',
		'car' => 'cars'
	);

	/**
	 * Tests is_countable
	 *
	 * @covers  \Halcyon\Utility\Inflector::is_countable
	 * @return  void
	 **/
	public function testIsCountable()
	{
		foreach (self::$uncountable_words as $word)
		{
			$result = Inflector::is_countable($word);

			$this->assertFalse($result);
		}

		foreach (self::$strings as $word)
		{
			$result = Inflector::is_countable($word);

			$this->assertTrue($result);
		}
	}

	/**
	 * Tests pluralizing words
	 *
	 * @covers  \Halcyon\Utility\Inflector::pluralize
	 * @return  void
	 **/
	public function testPluralize()
	{
		foreach (self::$strings as $singular => $plural)
		{
			$result = Inflector::pluralize($singular);

			$this->assertEquals($result, $plural);
		}

		foreach (self::$uncountable_words as $word)
		{
			$result = Inflector::pluralize($word);

			$this->assertEquals($result, $word);
		}
	}

	/**
	 * Tests singularizing words
	 *
	 * @covers  \Halcyon\Utility\Inflector::singularize
	 * @return  void
	 **/
	public function testSingularize()
	{
		foreach (self::$strings as $singular => $plural)
		{
			$result = Inflector::singularize($plural);

			$this->assertEquals($result, $singular);
		}

		foreach (self::$uncountable_words as $word)
		{
			$result = Inflector::singularize($word);

			$this->assertEquals($result, $word);
		}
	}
}
