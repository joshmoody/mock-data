<?php

namespace joshmoody\Mock\Tests;

use joshmoody\Mock\Generator;

class GeneratorTests extends \PHPUnit_Framework_TestCase
{
	public $generator;
	
	public function __construct()
	{
		$this->generator = new Generator();
/*
		$opts = array();
		$opts['hostname'] = getenv('db_hostname');
		$opts['username'] = getenv('db_username');
		$opts['password'] = getenv('db_password');
		$opts['database'] = getenv('db_database');
		$opts['dbdriver'] = getenv('db_driver');

		$this->generator = new Generator($opts);
*/
	}
	
/*
	public function testGeneratesPerson()
	{
		$person = $this->generator->getPerson();
		print_r($person);
		$this->assertObjectHasAttribute('guid', $person);
	}
*/
	
	public function testValidFloat()
	{
		$value = $this->generator->getFloat();
		$this->assertInternalType('float', $value);
	}
	
	public function testValidFloatRange()
	{
		$min = 100;
		$max = 200;
		$value = $this->generator->getFloat($min, $max);
		$this->assertTrue($value >= $min && $value <= $max, 'Float in defined range');
	}

	public function testValidFloatPrecision()
	{
		$min = 100;
		$max = 200;
		$precision = 4;
		$value = $this->generator->getFloat($min, $max, $precision);
		
		// This is ugly, but it's late and I need to find the number of decimal points.
		$parts = explode('.', $value);
		$decimals = strlen($parts[1]);
		
		$this->assertEquals($precision, $decimals, 'Float with correct precision');
	}
	
	public function testValidInteger()
	{
		$value = $this->generator->getInteger();
		$this->assertInternalType('integer', $value);
	}

	public function testValidIntegerRange()
	{
		$min = 100;
		$max = 200;
		$value = $this->generator->getInteger($min, $max);
		$this->assertTrue($value >= $min && $value <= $max, 'Integer in defined range');
	}
	
	public function testValidGender(){
		$gender = $this->generator->getGender();
		
		$this->assertTrue($gender == 'M' || $gender == 'F');	
	}
	
	public function testGeneratesName()
	{
		$name = $this->generator->getFullName();
		
		$this->assertObjectHasAttribute('first', $name);
		$this->assertObjectHasAttribute('middle', $name);
		$this->assertObjectHasAttribute('last', $name);
		$this->assertObjectHasAttribute('gender', $name);
	}
	
	public function testValidPhone()
	{
		$phone = $this->generator->getPhone('AR', '72201');
		$this->assertRegExp('/^\d{3}-\d{3}-\d{4}$/', $phone);
	}
}