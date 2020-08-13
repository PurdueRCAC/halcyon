<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Config\Tests;

use App\Halcyon\Test\Basic;
use App\Halcyon\Config\Processor;

/**
 * Processor tests
 */
class ProcessorTest extends Basic
{
	/**
	 * Tests all()
	 *
	 * @covers  \App\Halcyon\Config\Processor::all
	 * @return  void
	 **/
	public function testAll()
	{
		$instances = Processor::all();

		$this->assertCount(5, $instances);

		foreach ($instances as $instance)
		{
			$this->assertInstanceOf(Processor::class, $instance);
		}
	}

	/**
	 * Tests the instance() method
	 *
	 * @covers  \App\Halcyon\Config\Processor::instance
	 * @return  void
	 **/
	public function testInstance()
	{
		foreach (array('ini', 'yaml', 'json', 'php', 'xml') as $type)
		{
			$result = Processor::instance($type);

			$this->assertInstanceOf(Processor::class, $result);
		}

		$this->setExpectedException('App\Halcyon\\Error\\Exception\\InvalidArgumentException');

		$result = Processor::instance('py');
	}

	/**
	 * Tests getSupportedExtensions()
	 *
	 * @covers  \App\Halcyon\Config\Processor::getSupportedExtensions
	 * @return  void
	 **/
	public function testGetSupportedExtensions()
	{
		$stub = $this->getMockForAbstractClass('App\Halcyon\Config\Processor');
		$stub->expects($this->any())
			->method('getSupportedExtensions')
			->will($this->returnValue(array()));

		$this->assertEquals(array(), $stub->getSupportedExtensions());
	}

	/**
	 * Tests parse()
	 *
	 * @covers  \App\Halcyon\Config\Processor::parse
	 * @return  void
	 **/
	public function testParse()
	{
		$stub = $this->getMockForAbstractClass('App\Halcyon\Config\Processor');
		$stub->expects($this->any())
			->method('parse')
			->with($this->isType('string'))
			->will($this->returnValue(array()));

		$this->assertEquals(array(), $stub->parse(__DIR__ . '/Tests/Files/test.json'));
	}
}
