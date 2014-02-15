<?php

namespace joshmoody\Mock\Tests;

use joshmoody\Mock\Generator;
use joshmoody\Mock\Models\Database;

class GeneratorTests extends \PHPUnit_Framework_TestCase
{
	public $generator;
	public $date_regex = '/^((19|20))\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/';
	public $credit_card_regex = '/^[\d]{15,16}$/';
	public $credit_card_expiration_date_regex = '/^(0[1-9]|1[012])\/(19|20)\d\d$/';
	public $ssn_regex = '/^\d{9}$/';
	public $phone_regex = '/^\d{3}-\d{3}-\d{4}$/';

	public function __construct()
	{
		$dsn = Database::parseDsn(getenv('dsn'));
		$this->generator = new Generator(['dsn' => $dsn]);
	}
	
	public function testGeneratesPerson()
	{
		$person = $this->generator->getPerson();

		$this->assertObjectHasAttribute('guid', $person);
		$this->assertObjectHasAttribute('unique_hash', $person);
		$this->assertObjectHasAttribute('name', $person);
		$this->assertObjectHasAttribute('company', $person);
		$this->assertObjectHasAttribute('address', $person);
		$this->assertObjectHasAttribute('address2', $person);
		$this->assertObjectHasAttribute('internet', $person);
		$this->assertObjectHasAttribute('phone', $person);
		$this->assertObjectHasAttribute('ssn', $person);
		$this->assertObjectHasAttribute('dln', $person);
		$this->assertObjectHasAttribute('dob', $person);
		$this->assertObjectHasAttribute('credit_card', $person);
		$this->assertObjectHasAttribute('bank_account', $person);
	}
	
	public function testValidFloat()
	{
		$value = $this->generator->getFloat();
		$this->assertInternalType('float', $value);
	}
	
	public function testValidSsn()
	{
		$value = $this->generator->getSsn();
		$this->assertRegExp($this->ssn_regex, (string) $value);
	}

	public function testValidSsnForState()
	{
		$value = $this->generator->getSsn('AR');
		$this->assertRegExp($this->ssn_regex, (string) $value);
	}

	public function testValidSsnForUnknownState()
	{
		$value = $this->generator->getSsn('Foo');
		$this->assertRegExp($this->ssn_regex, (string) $value);
	}
	
	public function testValidDln()
	{
		$value = $this->generator->getDln();
		$this->assertObjectHasAttribute('number', $value);
		$this->assertObjectHasAttribute('state', $value);
		$this->assertObjectHasAttribute('expiration', $value);
	}
	
	public function testValidBirthDate(){
		$value = $this->generator->getBirthDate();
		$this->assertRegExp($this->date_regex, $value);
	}
	
	public function testValidCreditCard()
	{
		$value = $this->generator->getCreditCard();
		$this->assertObjectHasAttribute('type', $value);
		$this->assertObjectHasAttribute('number', $value);
		$this->assertObjectHasAttribute('expiration', $value);
		
		$this->assertRegExp($this->credit_card_regex, $value->number, 'Card number must be 15 or 16 digits.');
		$this->assertRegExp($this->credit_card_expiration_date_regex, $value->expiration, 'Expiration date should be mm/yyyy format.');
	}

	public function testValidBankAccount()
	{
		$value = $this->generator->getBank();
		$this->assertObjectHasAttribute('type', $value);
		$this->assertObjectHasAttribute('name', $value);
		$this->assertObjectHasAttribute('account', $value);
		$this->assertObjectHasAttribute('routing', $value);
	}
		
	public function testValidFloatRange()
	{
		$min = 1;
		$max = 5;
		$precision = 6;
		
		$value = $this->generator->getFloat($min, $max, $precision);
		
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
		
		$this->assertTrue($decimals <= $precision, 'Float with correct precision');
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
	
	public function testGeneratesFullName()
	{
		$name = $this->generator->getFullName();
		
		$this->assertObjectHasAttribute('first', $name);
		$this->assertObjectHasAttribute('middle', $name);
		$this->assertObjectHasAttribute('last', $name);
		$this->assertObjectHasAttribute('gender', $name);
	}

	public function testGeneratesFullNameWithFemaleGender()
	{
		$name = $this->generator->getFullName('F');
		$this->assertEquals('F', $name->gender);
	}

	public function testGeneratesFullNameWithMaleGender()
	{
		$name = $this->generator->getFullName('M');
		$this->assertEquals('M', $name->gender);
	}
	
	public function testGeneratesFirstNameNoGender()
	{
		$name = $this->generator->getFirstName();
		$this->assertInternalType('string', $name);
	}
	
	public function testGeneratesAddress()
	{
		$address = $this->generator->getAddress();
		$this->assertObjectHasAttribute('line_1', $address);
		$this->assertObjectHasAttribute('line_2', $address);
		$this->assertObjectHasAttribute('city', $address);
		$this->assertObjectHasAttribute('zip', $address);
		$this->assertObjectHasAttribute('county', $address);
		$this->assertObjectHasAttribute('state', $address);
	}

	public function testGeneratesInternet()
	{
		$internet = $this->generator->getInternet();

		$this->assertObjectHasAttribute('domain', $internet);
		$this->assertObjectHasAttribute('email', $internet);
		$this->assertObjectHasAttribute('url', $internet);
		$this->assertObjectHasAttribute('ip', $internet);
		$this->assertObjectHasAttribute('username', $internet);
	}
	
	public function testGeneratesDomain()
	{
		$domain = $this->generator->getDomain();
		$this->assertTrue(strlen($domain) > 3);
	}
	
	public function testValidPhone()
	{
		$phone = $this->generator->getPhone();
		$this->assertRegExp($this->phone_regex, $phone);
	}

	public function testValidPhoneWithState()
	{
		$phone = $this->generator->getPhone('AR');
		$this->assertRegExp($this->phone_regex, $phone);
	}

	public function testValidPhoneWithStateZip()
	{
		$phone = $this->generator->getPhone('AR', '72201');
		$this->assertRegExp($this->phone_regex, $phone);
	}
	
	public function testValidString()
	{
		$value = $this->generator->getString();
		$this->assertInternalType('string', $value);
	}

	public function testValidStringLettersOnly()
	{
		$value = $this->generator->getString('letter');
		$this->assertRegExp('/^[a-zA-Z]+$/', $value);
	}

	public function testValidStringNumbersOnly()
	{
		$value = $this->generator->getString('number');
		$this->assertRegExp('/^[0-9]+$/', $value);
	}
	
	public function testValidStringLength()
	{
		$value = $this->generator->getString(null, 5);
		$this->assertEquals(5, strlen($value));
	}
	
	public function testFromArrayNull()
	{
		$value = $this->generator->fromArray(null);
		$this->assertEquals(null, $value);
	}
	
	public function testGetDateNoMinYear()
	{
		$value = $this->generator->getDate(['max_year' => date('Y')], 'Y');
		$this->assertGreaterThanOrEqual(date('Y') - 2, $value);
	}

	public function testGetDateNoMaxYear()
	{
		$value = $this->generator->getDate(['min_year' => date('Y')], 'Y');
		$this->assertLessThanOrEqual(date('Y'), $value);
	}
}