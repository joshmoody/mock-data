<?php

namespace joshmoody\Mock\Tests;

use joshmoody\Mock\Generator;

class GeneratorTests extends \PHPUnit_Framework_TestCase
{
	public $generator;
	
	public function __construct()
	{
		//$this->generator = new joshmoody\Mock\Generator()
/*
		$opts = array();
		$opts['hostname'] = getenv('db_hostname');
		$opts['username'] = getenv('db_username');
		$opts['password'] = getenv('db_password');
		$opts['database'] = getenv('db_database');
		$opts['dbdriver'] = getenv('db_driver');

		$this->generator = new Generator($opts);
*/
		$this->generator = new Generator();
	}
	
	public function testGeneratesPerson()
	{
		$person = $this->generator->getPerson();
		
		print_r($person);
		
		$this->assertObjectHasAttribute('guid', $person);
	}
	
	public function testGeneratesName()
	{
		$name = $this->generator->getFullName();
		
		$this->assertObjectHasAttribute('first', $name);
		$this->assertObjectHasAttribute('middle', $name);
		$this->assertObjectHasAttribute('last', $name);
		$this->assertObjectHasAttribute('gender', $name);
	}
}