<?php

namespace joshmoody\Mock;

use Illuminate\Database\Capsule\Manager as DB;

use joshmoody\Mock\Models\Database;
use joshmoody\Mock\Models\LastName;
use joshmoody\Mock\Models\FirstName;
use joshmoody\Mock\Models\Street;
use joshmoody\Mock\Models\Zipcode;

use Exception;
use StdClass;
use DateTime;

class Generator
{

	public function __construct($opts = [])
	{
		if (is_array($opts) && array_key_exists('dsn', $opts)) {
			Database::init($opts['dsn']);
		} else {
			Database::init();
		}
	}

	/**
	 * Generate a float number between $min and $max, with precision $precision
	 */
	public function getFloat($min = 0, $max = 10000, $precision = 2)
	{
		$num = rand($min, $max) . '.' . str_pad(rand(1, 9999), $precision, '0', STR_PAD_LEFT);
		return round($num, $precision);
	}
	
	/**
	 * Generate a random number between $min and $max
	 */
	public function getInteger($min = 0, $max = 10000)
	{
		return rand($min, $max);
	}

	/**
	 * Generate a unique sha1 hash.
	 */
	public function getUniqueHash()
	{
		return sha1(uniqid(uniqid(), true));
	}
	
	/**
	 *	Generate random string.
	 * 
	 * @access public
	 * @param string $type Options: letter, number, or mix.	 default: letter
	 * @param mixed $desired_length Will be random length if not specified.
	 * @return string
	 */
	public function getString($type = 'letter', $desired_length = null)
	{
		if (empty($desired_length)) {
			$desired_length = $this->getInteger(1, 50);
		}
		
		$result = '';
		
		while (strlen($result) < $desired_length) {
			if ($type == 'letter') {
				$result .= $this->getLetter();
			} elseif ($type == 'number') {
				$result .= $this->getInteger(1, 10);
			} else {
				// Mix letters/numbers.
				$result .= $this->getUniqueHash();
			}
		}
		
		return substr($result, 0, $desired_length);
	}
	
	/**
	 * Generate a GUID.
	 */
	public function getGuid()
	{
		return sprintf(
						'%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
						mt_rand(0, 65535),
						mt_rand(0, 65535),	// 32 bits for "time_low"
						mt_rand(0, 65535),	// 16 bits for "time_mid"
						mt_rand(0, 4095),	// 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
						bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
						// 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
						// (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
						// 8 bits for "clk_seq_low"
						mt_rand(0, 65535),
						mt_rand(0, 65535),
						mt_rand(0, 65535) // 48 bits for "node"
		);
	}
	

	/**
	 * Return a random value from an array
	 */
	public function fromArray($array = [])
	{
		if (is_array($array) && count($array) > 0) {
			return $array[rand(0, count($array)-1)];
		} else {
			return null;
		}
	}
	
	/**
	 * Generate a boolean. Use the parameters to custom the ressponse to be true/false, 1/0, Yes/No, etc.
	 * 
	 * @access public
	 * @param mixed $true Value that should be returned if true (default: true)
	 * @param mixed $false Value that should be returned if false (default: false)
	 * @param mixed $likely How likely is it (1-10) the result will be true?  1 = Always, 10 = Almost never.
	 * @return mixed the result.
	 */
	public function getBool($true = true, $false = false, $likely = 2)
	{
		$i = $this->getInteger(1, 100);
		
		if ($i % $likely == 0) {
			return $true;
		} else {
			return $false;
		}
	}

	/**
	 * Generate a random date.
	 *
	 * @param array $params Associative array with following keys: minYear, maxYear, minMonth, maxMonth
	 * @param string $format date() format for return value.  Default: Y-m-d
	 */
	public function getDate($params = [], $format = 'Y-m-d')
	{
		foreach ($params as $k => $v) {
			$$k = $v;
		}

		if (!isset($min_year)) {
			$min_year = date('Y') - 2;
		}
		
		if (!isset($max_year)) {
			$max_year = date('Y');
		}

		if (!isset($min_month)) {
			$min_month = 1;
		}
		
		if (!isset($max_month)) {
			$max_month = 12;
		}
		
		// Pick a random year and month within the valid ranges.
		$rand_year	= rand($min_year, $max_year);
		$rand_month	= rand($min_month, $max_month);

		// Create a date object using the first day of this random month/year.
		$date = DateTime::createFromFormat('Y-m-d', join('-', [$rand_year, $rand_month, '01']));
		
		// How many days in this random month?
		$days_in_month = $date->format('t');

		// Pick a day of the month.
		$rand_day = rand(1, $days_in_month);
		
		return DateTime::createFromFormat('Y-m-d', join('-', [$rand_year, $rand_month, $rand_day]))->format($format);
	}
	
	/**
	 * Generate a reasonable birthdate.	 Default Age: 20-80 years.
	 */
	public function getBirthDate($params = [], $format = 'Y-m-d')
	{
		$params['min_year'] = array_key_exists('min_year', $params) ? $params['min_year'] : date('Y') - 80;
		$params['max_year'] = array_key_exists('max_year', $params) ? $params['max_year'] : date('Y') - 20;
		return $this->getDate($params, $format);
	}

	public function getExpiration($format = 'm/Y')
	{
		$date_params = ['min_year' => date('Y'), 'max_year' => date('Y') + 3];
		return $this->getDate($date_params, $format);
	}
	
	/**
	 * Returns a DLN object that contains a driver license number, state, and expiration
	 */
	public function getDln($state_code = null, $min = 900000001, $max = 999999999)
	{
		$dln = new stdclass();

		$dln->number		= rand($min, $max);
		$dln->state			= !empty($state_code) ? $state_code : $this->getState();
		$dln->expiration	= $this->getExpiration();
		
		return $dln;
	}

	/**
	 * Generate a 9 digit number that could be an SSN for a given state.
	 * SSN Prefixes from http://socialsecuritynumerology.com/prefixes.php for
	 */
	public function getSsn($state_code = null)
	{
		if (empty($state_code)) {
			$state_code = $this->getState()->code;
		}

		/**
		 Prefixes 580-xx-xxxx and up are allocated to US Territories and other states.
		 The below array structure does not support multiple prefix ranges, but this will do for now.
		 We are looking for FAKE data, not COMPLETE data.
		 */
		$ranges = [];
		$ranges['NH'] = ['min_prefix' => 1, 'max_prefix' => 3];
		$ranges['ME'] = ['min_prefix' => 4, 'max_prefix' => 7];
		$ranges['VT'] = ['min_prefix' => 8, 'max_prefix' => 9];
		$ranges['MA'] = ['min_prefix' => 10, 'max_prefix' => 34];
		$ranges['RI'] = ['min_prefix' => 35, 'max_prefix' => 39];
		$ranges['CT'] = ['min_prefix' => 40, 'max_prefix' => 49];
		$ranges['NY'] = ['min_prefix' => 50, 'max_prefix' => 134];
		$ranges['NJ'] = ['min_prefix' => 135, 'max_prefix' => 158];
		$ranges['PA'] = ['min_prefix' => 159, 'max_prefix' => 211];
		$ranges['MD'] = ['min_prefix' => 212, 'max_prefix' => 220];
		$ranges['DE'] = ['min_prefix' => 221, 'max_prefix' => 222];
		$ranges['VA'] = ['min_prefix' => 223, 'max_prefix' => 231];
		$ranges['WV'] = ['min_prefix' => 232, 'max_prefix' => 236];
		$ranges['NC'] = ['min_prefix' => 237, 'max_prefix' => 246];
		$ranges['SC'] = ['min_prefix' => 247, 'max_prefix' => 251];
		$ranges['GA'] = ['min_prefix' => 252, 'max_prefix' => 260];
		$ranges['FL'] = ['min_prefix' => 263, 'max_prefix' => 267];
		$ranges['OH'] = ['min_prefix' => 268, 'max_prefix' => 302];
		$ranges['IN'] = ['min_prefix' => 303, 'max_prefix' => 317];
		$ranges['IL'] = ['min_prefix' => 318, 'max_prefix' => 361];
		$ranges['MI'] = ['min_prefix' => 362, 'max_prefix' => 386];
		$ranges['WI'] = ['min_prefix' => 387, 'max_prefix' => 399];
		$ranges['KY'] = ['min_prefix' => 400, 'max_prefix' => 407];
		$ranges['TN'] = ['min_prefix' => 408, 'max_prefix' => 415];
		$ranges['AL'] = ['min_prefix' => 416, 'max_prefix' => 424];
		$ranges['MI'] = ['min_prefix' => 425, 'max_prefix' => 428];
		$ranges['AR'] = ['min_prefix' => 429, 'max_prefix' => 432];
		$ranges['LA'] = ['min_prefix' => 433, 'max_prefix' => 439];
		$ranges['OK'] = ['min_prefix' => 440, 'max_prefix' => 448];
		$ranges['TX'] = ['min_prefix' => 449, 'max_prefix' => 467];
		$ranges['MN'] = ['min_prefix' => 468, 'max_prefix' => 477];
		$ranges['IA'] = ['min_prefix' => 478, 'max_prefix' => 485];
		$ranges['MO'] = ['min_prefix' => 486, 'max_prefix' => 500];
		$ranges['ND'] = ['min_prefix' => 501, 'max_prefix' => 502];
		$ranges['SD'] = ['min_prefix' => 503, 'max_prefix' => 504];
		$ranges['NE'] = ['min_prefix' => 505, 'max_prefix' => 508];
		$ranges['KS'] = ['min_prefix' => 509, 'max_prefix' => 515];
		$ranges['MT'] = ['min_prefix' => 516, 'max_prefix' => 517];
		$ranges['ID'] = ['min_prefix' => 518, 'max_prefix' => 519];
		$ranges['WY'] = ['min_prefix' => 520, 'max_prefix' => 520];
		$ranges['CO'] = ['min_prefix' => 521, 'max_prefix' => 524];
		$ranges['NM'] = ['min_prefix' => 525, 'max_prefix' => 525];
		$ranges['AZ'] = ['min_prefix' => 526, 'max_prefix' => 527];
		$ranges['UT'] = ['min_prefix' => 528, 'max_prefix' => 529];
		$ranges['NV'] = ['min_prefix' => 530, 'max_prefix' => 530];
		$ranges['WA'] = ['min_prefix' => 531, 'max_prefix' => 539];
		$ranges['OR'] = ['min_prefix' => 540, 'max_prefix' => 544];
		$ranges['CA'] = ['min_prefix' => 545, 'max_prefix' => 573];
		$ranges['AK'] = ['min_prefix' => 574, 'max_prefix' => 574];
		$ranges['DC'] = ['min_prefix' => 577, 'max_prefix' => 579];

		if (!array_key_exists($state_code, $ranges)) {
			// We don't have a range for this state. Choose a state at random from the list.
			$state_code = $this->fromArray(array_keys($ranges));
		}
		
		$prefix = rand($ranges[$state_code]['min_prefix'], $ranges[$state_code]['min_prefix']);
		$suffix = rand(100000, 999999);
		return str_pad($prefix, 3, '0', STR_PAD_LEFT) . str_pad($suffix, 6, '0', STR_PAD_LEFT);
	}

	/**
	 * Generate a First Name
	 *
	 * Uses US Census data to get 250 most popular names for both male and female
	 *
	 * @param string $gender Do you want a male or female name? (M/F).	If null, selects a gender at random.
	 */
	public function getFirstName($gender = null)
	{
		if (empty($gender)) {
			$gender = $this->getGender();
		}

		return FirstName::where('gender', $gender)->where('rank', '<=', 250)->orderByRaw(Database::random())->first()->name;
	}
	
	/**
	 * Returns Gender (M/F) at random.
	 */
	public function getGender()
	{
		return $this->fromArray(['F', 'M']);
	}

	/**
	 * Generate a Last Name
	 *
	 * Uses US Census data to get $max most popular names for both male and female and selects one at random
	 *
	 * Pool is only 250 most frequent last names.  Increase by passing a higher value for $max
	 *
	 * @param int $max How large should our pool of names be? Default: 250
	 */
	public function getLastName($max = 250)
	{
		return LastName::where('rank', '<=', $max)->orderByRaw(Database::random())->first()->name;
	}
	
	/**
	 * Alias for get_firstname()
	 */
	public function getMiddleName($gender = null)
	{
		return $this->getFirstName($gender);
	}

	/**
	 * Returns a random character, A-Z
	 */
	public function getLetter()
	{
		$letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
		return $this->fromArray($letters);
	}
	
	/**
	 * Returns a Full Name
	 *
	 * @param string $gender.  Will be used to make sure both First and Middle Name are for same gender.
	 * @return object Object with first, middle, last name and gender.	Gender included to avoid "A Boy Named Sue".
	 */
	public function getFullName($gender = null)
	{
		if (empty($gender)) {
			$gender = $this->getGender();
		}
		
		$person_name = new stdclass();
		$person_name->first		= $this->getFirstName($gender);
		$person_name->middle	= $this->getMiddleName($gender);
		$person_name->last		= $this->getLastName();
		$person_name->gender	= $gender;
		
		return $person_name;
	}
	
	/**
	 * Return a street name
	 */
	public function getStreet()
	{
		$number = rand(100, 9999);
		
		$street_name = Street::orderByRaw(Database::random())->first()->name;

		return $number . ' ' . $street_name;
	}
	
	public function getApartment()
	{
		$types = ['Apt.', 'Apartment', 'Ste.', 'Suite', 'Box'];
		
		if ($this->getBool(true, false)) {
			$extra = $this->getLetter();
		} else {
			$extra = $this->getInteger(1, 9999);
		}
		
		$type = $this->fromArray($types);
		return $type . ' ' . $extra;
	}
	
	/**
	 * Return a state
	 *
	 * @return array(code, description)
	 */
	public function getState($state_code = null)
	{
		
		if (!empty($state_code)) {
			$res = Zipcode::where('state_code', $state_code)->orderByRaw(Database::random())->first();
		} else {
			$res = Zipcode::orderByRaw(Database::random())->first();
		}
		
		$State = new stdclass();
		$State->code = $res->state_code;
		$State->name = $res->state;
		return $State;
	}
	
	/**
	 * Return a zip code
	 */
	public function getZip($state_code = null)
	{
	
		if (!empty($state_code)) {
			return Zipcode::where('state_code', $state_code)->orderByRaw(Database::random())->first()->zip;
		} else {
			return Zipcode::orderByRaw(Database::random())->first()->zip;
		}
	}

	public function getCity($state_code = false)
	{
		if ($state_code) {
			return Zipcode::where('state_code', $state_code)->orderByRaw(Database::random())->first()->city;
		} else {
			return Zipcode::orderByRaw(Database::random())->first()->city;
		}
	}
	
	/**
	 * Return object with full street info
	 */
	public function getAddress($state_code = null, $zip = null)
	{
		$address = new stdclass();

		if (!empty($zip) && !empty($state_code)) {
			$result = Zipcode::where('zip', $zip)->where('state_code', $state_code)->orderByRaw(Database::random())->first();
		} elseif (!empty($zip)) {
			$result = Zipcode::where('zip', $zip)->orderByRaw(Database::random())->first();
		} elseif (!empty($state_code)) {
			$result = Zipcode::where('state_code', $state_code)->orderByRaw(Database::random())->first();
		} else {
			$result = Zipcode::orderByRaw(Database::random())->first();
		}

		$address->line_1 = $this->getStreet();
		
		if ($this->getBool(true, false)) {
			$address->line_2 = $this->getApartment();
		} else {
			$address->line_2 = null;
		}
		
		$address->city = $result->city;
		$address->zip = $result->zip;
		$address->county = $result->county;
		
		$address->state = new stdclass();
		$address->state->code = $result->state_code;
		$address->state->name = $result->state;
		
		return $address;
	}
	
	/**
	 * Return a Company Name.  Uses a random last name plus a suffix that looks like a company name.
	 * You can optionally pass a name to serve as the prefix
	 */
	public function getCompanyName($base_name = null)
	{
		$suffixes = ['Corporation', 'Company', 'Company, Limited', 'Computer Repair', 'Incorporated', 'and Sons', 'Group', 'Group, PLC', 'Furniture', 'Flowers', 'Sales', 'Systems', 'Tire', 'Auto', 'Plumbing', 'Roofing', 'Realty', 'Foods', 'Books'];
		
		if (empty($base_name)) {
			$base_name = $this->getLastName();
		}

		return $base_name . ' ' . $this->fromArray($suffixes);
	}
	
	/**
	 * Return a phone number
	 */
	public function getPhone($state_code = null, $zip = null, $include_toll_free = false)
	{
		if (!empty($zip)) {
			$areacodes = Zipcode::where('zip', $zip)->orderByRaw(Database::random())->first()->area_codes;
		} else {
			// Get a random state if state not provided
			$state_code = !empty($state_code) ? $state_code : $this->getState()->code;
			
			// Get area codes appropriate for this state
			$areacodes = Zipcode::where('state_code', $state_code)->orderByRaw(Database::random())->first()->area_codes;
		}

		// Get list of valid area codes for the state/zip code
		$code_list = explode(',', $areacodes);
		
		// Add some toll free numbers into the mix.
		if ($include_toll_free === true) {
			$code_list[] = 800;
			$code_list[] = 888;
			$code_list[] = 877;
			$code_list[] = 866;
			$code_list[] = 855;
		}
		
		// Get a random area code from valid area codes
		$areacode	= $this->fromArray($code_list);
		$prefix		= rand(100, 999);
		$number		= rand(1, 9999);
		
		return $areacode . '-' . $prefix . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
	}
	
	public function getDomain($base = null)
	{
		$domain = !empty($base) ? $base : $this->getLastName();
		
		$domain = preg_replace('/[^0-9a-z_A-Z]/', '', $domain);

		$tld = ['.com', '.net', '.us', '.biz'];
		return strtolower($domain) . $this->fromArray($tld);
	}
	
	public function getUrl($domain = null)
	{
		$protocol = ['https://www.', 'http://www.', 'http://', 'https://'];
		
		$domain = !empty($domain) ? $domain : $this->getDomain();
		
		return $this->fromArray($protocol)	. $domain;
	}
	
	public function getIp()
	{
		$parts = [];
		
		for ($i=0; $i<4; $i++) {
			$parts[] = $this->getInteger(0, 255);
		}
		
		return join('.', $parts);
	}
	
	public function getUsername($person_name = null)
	{
		if (empty($person_name)) {
			$person_name = $this->getFullName();
		}

		$usernames = [];
		
		# Example Person Name: John Doe.
		
		$usernames[] = $person_name->first; // john
		$usernames[] = $person_name->last; // doe
		$usernames[] = $person_name->first . '.' . $person_name->last; // john.doe
		$usernames[] = $person_name->first . $person_name->last; // johndoe
		$usernames[] = substr($person_name->first, 0, 1) . $person_name->last; //jdoe

		return strtolower($this->fromArray($usernames));
	}
	
	/**
	 * Return an email address.
	 * You can optionally pass a name to use in the address
	 */
	public function getEmail($person_name = null, $domain = null)
	{
		$username = $this->getUsername($person_name);
		
		$domains = [];
		$domains[] = !empty($domain) ? $domain : $this->getDomain();
		$domains[] = 'gmail.com';
		$domains[] = 'yahoo.com';
		$domains[] = 'me.com';
		$domains[] = 'msn.com';
		$domains[] = 'hotmail.com';
		
		$domain = $this->fromArray($domains);
		
		return preg_replace('/[^0-9a-z_A-Z.]/', '', strtolower($username)) . '@' . $domain;
	}
	
	/**
	 * Generate internet information.  Domain, Email, URL, IP Address, Username
	 * 
	 * @access public
	 * @param mixed $person_name (default: null)
	 * @param mixed $company (default: null)
	 * @return Internet object
	 */
	public function getInternet($person_name = null, $company = null)
	{
		if (empty($person_name)) {
			$person_name = $this->getFullName();
		}
		
		$internet = new stdclass();
		$internet->domain	= $this->getDomain($company);
		$internet->username	= $this->getUserName($person_name);
		$internet->email	= $this->getEmail($person_name, $internet->domain);
		$internet->url		= $this->getUrl($internet->domain);
		$internet->ip		= $this->getIp();
		
		return $internet;
	}
		
	/**
	 * Generate a credit card number.
	 * 
	 * @access public
	 * @param mixed $weighted (default: true) - Make it more likely to return MasterCard or Visa
	 * @return CreditCard Object.  
	 */
	public function getCreditCard($weighted = true)
	{
		// Get a random card type

		if ($weighted) {
			$weight[] = ['American Express', 1];
			$weight[] = ['Discover', 2];
			$weight[] = ['MasterCard', 10];
			$weight[] = ['Visa', 10];
			
			foreach ($weight as $w) {
				$type = $w[0];
				$count = $w[1];
			
				for ($i=0; $i<$count; $i++) {
					$card_types[] = $type;
				}
			}
		} else {
			$card_types = ['American Express', 'Discover', 'MasterCard', 'Visa'];
		}

		$cc = new stdclass();
				
		$cc->type = $this->fromArray($card_types);

		// Get a random card number appropriate for this type that passes Luhn/Mod10 check
		$cc->number = $this->getBankNumber($cc->type);
		
		// Get an expiration date
		$cc->expiration = $this->getExpiration();

		return $cc;
	}
	
	
	/**
	 * Generate bank account information.
	 * 
	 * @access public
	 * @return BankAccount object
	 */
	public function getBank()
	{
		$bank_account = new stdclass();
	
		$bank_account->type = $this->fromArray(['Checking', 'Savings']);
		$bank_account->name = $this->fromArray(['First National', 'Arvest', 'Regions', 'Metropolitan', 'Wells Fargo']);
		
		$bank_account->account = $this->getInteger('1000', '999999999');
		$bank_account->routing = $this->getBankNumber('Routing');
		
		return $bank_account;
	}
	
	/**
	 * Generate a Person object with all relevent attributes.
	 * 
	 * @access public
	 * @param mixed $state_code (default: null)
	 * @return void
	 */
	public function getPerson($state_code = null)
	{
		$state_code = !empty($state_code) ? $state_code : $this->getState()->code;
		
		$person = new stdclass();

		$person->guid = $this->getGuid();
		$person->unique_hash = $this->getUniqueHash();
		
		$person->name = $this->getFullName(); // Returns an object with first, middle, last, and gender properties
		
		if (rand(1, 100) % 5 == 0) {
			// Self employed?  Name the business after them.
			$person->company = $this->getCompanyName($person->name->last);
		} else {
			// Generate some random company name.
			$person->company = $this->getCompanyName();
		}

		# Primary address
		$person->address = $this->getAddress($state_code);
		
		# Secondary Address.  Mailing Address?	Use same zip code and primary address
		$person->address2 = $this->getAddress($state_code, $person->address->zip);
		
		$person->internet = $this->getInternet($person->name, $person->company);

		# Everyone has at least 2 or three phone numbers
		$person->phone	= new stdclass();
		$person->phone->home	= $this->getPhone($state_code, $person->address->zip);
		$person->phone->mobile	= $this->getPhone($state_code, $person->address->zip);
		$person->phone->work	= $this->getPhone($state_code, $person->address->zip);

		$person->ssn	= $this->getSsn($state_code);
		$person->dln	= $this->getDln($state_code);

		$person->dob	= $this->getBirthDate();

		# Payment Implements
		$person->credit_card = $this->getCreditCard();
		$person->bank_account = $this->getBank();

		return $person;
	}

	/**
	 * I've adapted a credit card / routing number generator to meet my needs here. Original copyright below.
	 *
	 * Numbers created here will pass the Luhn Mod-10 test.
	 */
	public function getBankNumber($type = 'Visa')
	{
		$visaPrefixList[] = '4539';
		$visaPrefixList[] = '4556';
		$visaPrefixList[] = '4916';
		$visaPrefixList[] = '4532';
		$visaPrefixList[] = '4929';
		$visaPrefixList[] = '40240071';
		$visaPrefixList[] = '4485';
		$visaPrefixList[] = '4716';
		$visaPrefixList[] = '4';
		
		$mastercardPrefixList[] = '51';
		$mastercardPrefixList[] = '52';
		$mastercardPrefixList[] = '53';
		$mastercardPrefixList[] = '54';
		$mastercardPrefixList[] = '55';
		
		$amexPrefixList[] = '34';
		$amexPrefixList[] = '37';
		
		$discoverPrefixList[] = '6011';
		
		$routingPrefixList[] = '01';
		$routingPrefixList[] = '02';
		$routingPrefixList[] = '03';
		$routingPrefixList[] = '04';
		$routingPrefixList[] = '05';
		$routingPrefixList[] = '06';
		$routingPrefixList[] = '07';
		$routingPrefixList[] = '08';
		$routingPrefixList[] = '09';
		$routingPrefixList[] = '10';
		$routingPrefixList[] = '11';
		$routingPrefixList[] = '12';
		
		switch ($type) {
			case 'Visa':
				$bank_number = $this->completedBankNumber($visaPrefixList, 16);
				break;
			case 'Master Card':
				$bank_number = $this->completedBankNumber($mastercardPrefixList, 16);
				break;
			case 'American Express':
				$bank_number = $this->completedBankNumber($amexPrefixList, 15);
				break;
			case 'Discover':
				$bank_number = $this->completedBankNumber($discoverPrefixList, 16);
				break;
			case 'Routing':
				$bank_number = $this->completedBankNumber($routingPrefixList, 9);
				break;
			default:
				$bank_number = $this->completedBankNumber($visaPrefixList, 16);
				break;
		}
		
		return $bank_number;
	}
		
	/**
	 * 'prefix' is the start of the CC number as a string, any number of digits.
	 * 'length' is the length of the CC number to generate. Typically 13 or 16
	 */
	protected function completedBankNumber($prefixlist, $length)
	{
	
		$prefix = $prefixlist[rand(0, count($prefixlist)-1)];
		$ccnumber = $prefix;
	
		# generate digits
	
		while (strlen($ccnumber) < ($length - 1)) {
			$ccnumber .= rand(0, 9);
		}
	
		# Calculate sum
	
		$sum = 0;
		$pos = 0;
	
		$reversedCCnumber = strrev($ccnumber);
	
		while ($pos < $length - 1) {
	
			$odd = $reversedCCnumber[ $pos ] * 2;
	
			if ($odd > 9) {
				$odd -= 9;
			}
	
			$sum += $odd;
	
			if ($pos != ($length - 2)) {
				$sum += $reversedCCnumber[ $pos +1 ];
			}
	
			$pos += 2;
		}
	
		# Calculate check digit
	
		$checkdigit = ((floor($sum/10) + 1) * 10 - $sum) % 10;
		$ccnumber .= $checkdigit;
	
		return $ccnumber;
	}
}

/**
 * The credit card/bank account generator is not my code, I've only modified slightly to meet my needs.
 * Original copyright below.
 */

/**
 * PHP credit card number generator
 * Copyright (C) 2006 Graham King graham@darkcoding.net
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
