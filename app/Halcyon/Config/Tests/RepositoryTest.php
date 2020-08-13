<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Config\Tests;

use App\Halcyon\Test\Basic;
use App\Halcyon\Config\Repository;
use App\Halcyon\Config\FileLoader;
use stdClass;

/**
 * Repository tests
 */
class RepositoryTest extends Basic
{
	/**
	 * Tests the constructor sets loader and client
	 *
	 * @covers  \App\Halcyon\Config\Repository::__construct
	 * @covers  \App\Halcyon\Config\Repository::setLoader
	 * @covers  \App\Halcyon\Config\Repository::getLoader
	 * @covers  \App\Halcyon\Config\Repository::setClient
	 * @covers  \App\Halcyon\Config\Repository::getClient
	 * @return  void
	 **/
	public function testConstructor()
	{
		$data = new Repository('site');

		// Test set and get Client
		$this->assertEquals($data->getClient(), 'site');

		$data->setClient('api');

		$this->assertEquals($data->getClient(), 'api');

		// Test that a default loader was set
		$this->assertInstanceOf('App\Halcyon\Config\FileLoader', $data->getLoader());

		// Test setting a loader
		$path = __DIR__ . '/Files/Repository';
		$loader = new FileLoader($path);

		// Set by method
		$data->setLoader($loader);

		$this->assertInstanceOf('App\Halcyon\Config\FileLoader', $data->getLoader());
		$this->assertEquals($path, $data->getLoader()->getDefaultPath());

		// Set by constructor
		$data = new Repository('files', $loader);

		$this->assertInstanceOf('App\Halcyon\Config\FileLoader', $data->getLoader());
		$this->assertEquals($path, $data->getLoader()->getDefaultPath());
	}

	/**
	 * Tests get()
	 *
	 * @covers  \App\Halcyon\Config\Repository::load
	 * @covers  \App\Halcyon\Config\Repository::get
	 * @return  void
	 **/
	public function testSetAndGet()
	{
		$loader = new FileLoader(__DIR__ . '/Files/Repository');

		$data = new Repository('site', $loader);

		// Test that default value is returned
		$this->assertEquals($data->get('foo'), null);
		$this->assertEquals($data->get('foo', 'one'), 'one');
		$this->assertEquals($data->get('lorem.ipsum.dolor', 'baz'), 'baz');
		$this->assertEquals($data->get('app.application_env'), 'development');
		$this->assertEquals($data->get('application_env'), 'development');

		$loader = new FileLoader(__DIR__ . '/Files/Repository');

		$data = new Repository('api', $loader);
		$this->assertEquals($data->get('app.application_env'), 'production');

		// Test correct value is returned
		$data->set('foo', 'bar');

		$this->assertEquals($data->get('foo'), 'bar');

		$data->set('lorem', new stdClass);
		$data->set('lorem.ipsum', 'sham');

		$this->assertEquals($data->get('lorem.ipsum'), 'sham');

		$data['foo'] = 'lorem';

		$this->assertEquals($data->get('', 'lorem'), 'lorem');
		$this->assertEquals($data->get('foo'), 'lorem');
		$this->assertEquals($data['foo'], 'lorem');
		$this->assertEquals($data->get('fake.path', 'lorem'), 'lorem');

		$data['lorem.ipsum'] = 'ipsum';

		$this->assertEquals($data->get('lorem.ipsum'), 'ipsum');
		$this->assertEquals($data['lorem.ipsum'], 'ipsum');
		$this->assertEquals($data->get('lorem.dolor', 'mit'), 'mit');

		$data['lorem'] = array('ipsum' => 'dolor');

		$this->assertEquals($data->get('ipsum'), 'dolor');

		$data->set('lorem.ipsum', array('dolor' => 'mit'));

		$this->assertEquals($data->get('lorem.ipsum.dolor'), 'mit');

		$data->set('lorem', array('ipsum' => 'dolor'));
		$data->set('lorem.dolor.foo', 'bar');

		$this->assertEquals($data->get('lorem.dolor.foo'), 'bar');
	}
}
