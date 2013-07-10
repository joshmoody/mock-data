<?php

namespace joshmoody\Mock;

use PDO;
use Exception;
use StdClass;

class Generator{

	protected $db;
	
	function __construct($opts = array('sqlite' => TRUE))
	{
		$hostname = NULL;
		$username = NULL;
		$password = NULL;
		$database = NULL;
		$dbdriver = NULL;
		$sqlite   = FALSE;
		
		extract($opts, EXTR_IF_EXISTS);

		// Build PDO DSN.
		if ($sqlite === TRUE)
		{
			$db_path = __DIR__ . '/database.sqlite';
			$temp_path = '/tmp/database.sqlite';
			copy($db_path, $temp_path);
			$dsn = sprintf('sqlite:%s', $temp_path);
		}
		else if ($dbdriver == 'sqlite')
		{
			$dsn = sprintf('sqlite:%s', $database);
		}
		else
		{
			$dsn = sprintf('%s:host=%s;dbname=%s', $dbdriver, $hostname, $database);
		}
		
		try
		{
			$this->db = new PDO($dsn, $username, $password);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Generate a float number between $min and $max, with precision $precision
	 */
	public function getFloat($min=0, $max=10000, $precision=2)
	{
		$num = rand($min, $max) . '.' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
		return round($num, $precision);
	}
	
	/**
	 * Generate a random number between $min and $max
	 */
	public function getInteger($min=0, $max=10000)
	{
		return rand($min, $max);
	}

	/**
	 * Generate a unique sha1 hash.
	 */
	public function getUniqueHash()
	{
		return sha1(uniqid(uniqid(), TRUE));
	}
	
	/**
	 *  Generate random string.
	 * 
	 * @access public
	 * @param string $type Options: letter, number, or mix.  default: letter
	 * @param mixed $desired_length Will be random length if not specified.
	 * @return string
	 */
	public function getString($type = 'letter', $desired_length = FALSE)
	{
		if (!$desired_length)
		{
			$desired_length = $this->getInteger(1, 50);
		}
		
		$result = '';
		
		while (strlen($result) < $desired_length)
		{
			if ($type == 'letter')
			{
				$result .= $this->getLetter();
			}
			else if ($type == 'number')
			{
				$result .= $this->getInteger(1, 10);
			}
			else
			{
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
		return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
			mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
			mt_rand(0, 65535), // 16 bits for "time_mid"
			mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
			bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
				// 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
				// (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
				// 8 bits for "clk_seq_low"
			mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
		);
	}
	

	/**
	 * Return a random value from an array
	 */
	public function fromArray($array = array())
	{
		if (count($array) > 0)
		{
			return $array[rand(0, count($array)-1)];
		}
		else
		{
			return FALSE;			
		}
	}
	
	/**
	 * Generate a boolean. Use the parameters to custom the ressponse to be TRUE/FALSE, 1/0, Yes/No, etc.
	 * 
	 * @access public
	 * @param mixed $true Value that should be returned if TRUE (default: TRUE)
	 * @param mixed $false Value that should be returned if FALSE (default: FALSE)
	 * @param mixed $likely How likely is it (1-10) the result will be TRUE?  1 = Always, 10 = Almost never.
	 * @return mixed the result.
	 */
	public function getBool($true = TRUE, $false = FALSE, $likely = 2)
	{	
		$i = $this->getInteger(1,100);
		
		if ($i % $likely == 0)
		{
			return $true;
		}
		else
		{
			return $false;
		}
	}

	/**
	 * Generate a random date.
	 *
	 * @param array $params Associative array with following keys: minYear, maxYear, minMonth, maxMonth
	 * @param string $format date() format for return value.  Default: Y-m-d
	 */
	public function getDate($params=array(), $format = 'Y-m-d')
	{
		foreach($params as $k=>$v)
		{
			$$k = $v;
		}

		if (!isset($min_year))
		{
			$min_year = date('Y') - 2;
		}
		
		if (!isset($max_year))
		{
			$max_year = date('Y');
		}

		if (!isset($min_month))
		{
			$min_month = 1;
		}
		
		if (!isset($max_month))
		{
			$max_month = 12;
		}
		
		$rand_year		= rand($min_year, $max_year);
		$rand_month		= rand($min_month, $max_month);
		$days_in_month	= date('t', strtotime($rand_year . '/' . $rand_month . '/' . '01'));
		$rand_day		= rand(1, $days_in_month);
		
		return date($format, strtotime($rand_year . '/' . $rand_month . '/' . $rand_day));
	}
	
	/**
	 * Generate a reasonable birthdate.  Default Range: 1930-1990
	 */
	public function getBirthDate($params=array(), $format = 'Y-m-d')
	{
		$params['min_year'] = array_key_exists('min_year', $params) ? $params['min_year'] : 1930;
		$params['max_year'] = array_key_exists('max_year', $params) ? $params['max_year'] : 1980;
		return $this->getDate($params, $format);
	}

	public function getExpiration($format = 'm/Y')
	{
		$date_params = array('min_year' => date('Y'),
							 'max_year' => date('Y') + 3);
		return $this->getDate($date_params, $format);
	}
	
	/**
	 * Returns a DLN object that contains a driver license number, state, and expiration
	 */
	public function getDln($state_code = FALSE, $min=900000001, $max=999999999)
	{
		$dln = new stdclass();

		$dln->number 		= rand($min, $max);
		$dln->state 		= $state_code ? $this->getState($state_code) : $this->getState();		
		$dln->expiration	= $this->getExpiration();
		
		return $dln;
	}

	/**
	 * Generate a 9 digit number that could be an SSN.
	 * The default min and max denote numbers assigned in Arkansas. See http://socialsecuritynumerology.com/prefixes.php for other States' ranges.
	 */
	public function getSsn($min=429000001, $max=432999999)
	{
		return rand($min, $max);
	}

	/**
	 * Generate a First Name
	 *
	 * Uses US Census data to get 250 most popular names for both male and female
	 *
	 * @param string $gender Do you want a male or female name? (M/F).  If null, selects a gender at random.
	 */
	public function getFirstName($gender=NULL)
	{
		if ($gender == FALSE || $gender == NULL)
		{
			$gender = $this->getGender();
		}

		return $this->query('SELECT name FROM firstnames WHERE gender = :gender AND rank <= 250 ORDER BY RAND() LIMIT 1', array(':gender' => $gender))->fetch()->name;
	}
	
	/**
	 * Returns Gender (M/F) at random.
	 */
	public function getGender()
	{
		if (rand(1,100) % 2 == 0)
		{
			return 'F';
		}
		else
		{
			return 'M';
		}
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
		return $this->query('SELECT name FROM lastnames WHERE rank <= :rank ORDER BY RAND() LIMIT 1', array(':rank' => 250))->fetch()->name;
	}
	
	/**
	 * Alias for get_firstname()
	 */
	public function getMiddleName($gender=NULL)
	{
		return $this->getFirstName($gender);
	}

	/**
	 * Returns a random character, A-Z
	 */
	public function getLetter()
	{
		$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		return $this->fromArray($letters);
	}
	
	/**
	 * Returns a Full Name
	 *
	 * @param string $gender.  Will be used to make sure both First and Middle Name are for same gender.
	 * @return object Object with first, middle, last name and gender.  Gender included to avoid "A Boy Named Sue".
	 */
	public function getFullName($gender = FALSE)
	{
		if (!$gender)
		{
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
		
		$street_name = $this->query('SELECT name FROM streets ORDER BY RAND() LIMIT 1')->fetch()->name;
		
		return $number . ' ' . $street_name;
	}
	
	function getApartment()
	{
		$types = array('Apt.', 'Apartment', 'Ste.', 'Suite', 'Box');
		
		if ($this->getBool(TRUE,FALSE))
		{
			$extra = $this->getLetter();
		}
		else
		{
			$extra = $this->getInteger(1,9999);
		}
		
		$type = $this->fromArray($types);
		return $type . ' ' . $extra;
	}
	
	/**
	 * Return a state
	 *
	 * @return array(code, description)
	 */
	public function getState($state_code = FALSE){
		
		if ($state_code)
		{
			$res = $this->query('SELECT state_code, state FROM zipcodes WHERE state_code = :state_code ORDER BY RAND() LIMIT 1', array(':state_code' => $state_code))->fetch();
		}
		else
		{
			$res = $this->query('SELECT state_code, state FROM zipcodes ORDER BY RAND() LIMIT 1')->fetch();
		}
		
		$State = new stdclass();
		$State->code = $res->state_code;
		$State->name = $res->state;
		return $State;
	}
	
	/**
	 * Return a zip code
	 */
	public function getZip($state_code = FALSE){
	
		if ($state_code)
		{
			return $this->query('SELECT zip FROM zipcodes WHERE state_code = :state_code ORDER BY RAND() LIMIT 1', array(':state_code' => $state_code))->fetch()->zip;
		}
		else
		{
			return $this->query('SELECT zip from zipcodes ORDER BY RAND() LIMIT 1')->fetch()->zip;
		}
	}

	public function getCity($state_code = FALSE)
	{
		if ($state_code)
		{
			return $this->query('SELECT city FROM zipcodes WHERE state_code = :state_code ORDER BY RAND() LIMIT 1', array(':state_code' => $state_code))->fetch()->city;
		}
		else
		{
			return $this->query('SELECT city from zipcodes ORDER BY RAND() LIMIT 1')->fetch()->city;
		}
	}
	
	/**
	 * Return object with full street info
	 */
	public function getAddress($state_code = FALSE, $zip = FALSE)
	{
		$address = new stdclass();

		if ($zip)
		{
			$result = $this->query('SELECT city, state, state_code, zip, county FROM zipcodes WHERE zip = :zip ORDER BY RAND() LIMIT 1', array(':zip' => $zip))->fetch();			
		}
		else
		{
			if ($state_code)
			{
				$result = $this->query('SELECT city, state, state_code, zip, county FROM zipcodes WHERE state_code = :state_code ORDER BY RAND() LIMIT 1', array(':state_code' => $state_code))->fetch();
			}
			else
			{
				$result = $this->query('SELECT city, state, state_code, zip, county FROM zipcodes ORDER BY RAND() LIMIT 1')->fetch();
			}
		}
		

		$address->line_1 = $this->getStreet();
		
		if ($this->getBool(TRUE, FALSE))
		{
			$address->line_2 = $this->getApartment();	
		}
		else
		{
			$address->line_2 = NULL;
		}
		
		$address->city		= $result->city;
		$address->zip		= $result->zip;
		$address->county 	= $result->county;
		
		$address->state = new stdclass();
		$address->state->code = $result->state_code;
		$address->state->name = $result->state;
		
		return $address;
	}
	
	/**
	 * Return a Company Name.  Uses a random last name plus a suffix that looks like a company name.
	 * You can optionally pass a name to serve as the prefix
	 */
	public function getCompanyName($base_name = NULL)
	{
		$suffixes = array('Corporation', 'Company', 'Company, Limited', 'Computer Repair', 'Incorporated', 'and Sons', 'Group', 'Group, PLC', 'Furniture', 'Flowers', 'Sales', 'Systems', 'Tire', 'Auto', 'Plumbing', 'Roofing', 'Realty', 'Foods', 'Books');
		
		if (!$base_name)
		{
			$base_name = $this->getLastName();
		}

		return $base_name . ' ' . $this->fromArray($suffixes);
	}
	
	/**
	 * Return a phone number
	 */
	public function getPhone($state_code = FALSE, $zip_code = FALSE, $include_toll_free = FALSE)
	{

		if ($zip_code)
		{
			$areacodes = $this->query('SELECT area_codes FROM zipcodes WHERE zip = :zip ORDER BY RAND() LIMIT 1', array(':zip' => $zip_code))->fetch()->area_codes;
		}
		else
		{
			// Get a random state if state not provided
			$state_code = $state_code ? $state_code : $this->getState()->code;
			
			// Get area codes appropriate for this state
			$areacodes = $this->query('SELECT area_codes FROM zipcodes WHERE state_code = :state_code ORDER BY RAND() LIMIT 1', array(':state_code' => $state_code))->fetch()->area_codes;
		}

		// Get list of valid area codes for the state/zip code
		$code_list = explode(',', $areacodes);
		
		// Add some toll free numbers into the mix.
		if ($include_toll_free === TRUE)
		{
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
	
	public function getDomain($base = FALSE)
	{
		$domain = $base ? $base : $this->getLastName();
		
		$domain = preg_replace('/[^0-9a-z_A-Z]/', '', $domain);

		$tld = array('.com', '.net', '.us');
		return strtolower($domain) . $this->fromArray($tld);
	}
	
	public function getUrl($domain = FALSE)
	{
		$protocol = array('https://www.', 'http://www.', 'http://', 'https://');
		
		$domain = $domain ? $domain : $this->get_domain();
		
		return $this->fromArray($protocol)  . $domain;
	}
	
	public function getIp()
	{
		$parts = array();
		
		for ($i=0; $i<4; $i++)
		{
			$parts[] = $this->getInteger(0, 255);
		}
		
		return join('.', $parts);
	}
	
	/**
	 * Return an email address.
	 * You can optionally pass a name to use in the address
	 */
	 public function getEmail($person_name = FALSE, $domain = FALSE)
	 {
		if ($person_name == FALSE)
		{
			$person_name = $this->getFullName();
		}
		
		$account_options = array();
		$account_options[] = $person_name->first; // firstname@example.com
		$account_options[] = $person_name->last; // lastname@example.com
		$account_options[] = $person_name->first . '.' . $person_name->last; // firstname.lastname@example.com
		$account_options[] = $person_name->first . $person_name->last; // firstnamelastname@example.com
		
		$account = $this->fromArray($account_options);
		
		$domain_options = array();
		$domain_options[] = $domain ? $domain : $this->get_domain();
		$domain_options[] = 'gmail.com';
		$domain_options[] = 'yahoo.com';
		$domain_options[] = 'me.com';
		
		$domain = $this->fromArray($domain_options);
		
		return preg_replace('/[^0-9a-z_A-Z.]/', '', strtolower($account)) . '@' . $domain;
	}
	
	
	/**
	 * Generate a credit card number.
	 * 
	 * @access public
	 * @param mixed $weighted (default: TRUE) - Make it more likely to return MasterCard or Visa
	 * @return CreditCard Object.  
	 */
	function getCreditCard($weighted = TRUE)
	{
		// Get a random card type

		if ($weighted){
			$weight[] = array('American Express', 1);
			$weight[] = array('Discover'		, 2);
			$weight[] = array('MasterCard'		, 10);
			$weight[] = array('Visa'			, 10);
			
			foreach($weight as $w)
			{
				$type = $w[0];
				$count = $w[1];
			
				for($i=0; $i<$count; $i++)
				{
					$card_types[] = $type;
				}
			}
		}
		else
		{
			$card_types = array('American Express', 'Discover', 'MasterCard', 'Visa');
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
	
		$bank_types = array('Checking', 'Savings');
		$bank_account->type = $this->fromArray($bank_types);
		
		$bank_names = array('First National', 'Arvest', 'Regions', 'Metropolitan', 'Wells Fargo');
		$bank_account->name = $this->fromArray($bank_names);
		
		$bank_account->account = $this->getInteger('1000', '999999999');
		$bank_account->routing = $this->getBankNumber('Routing');
		
		return $bank_account;
	}
	
	
	/**
	 * Generate internet information.  Domain, Email, URL, IP Address
	 * 
	 * @access public
	 * @param mixed $person_name (default: FALSE)
	 * @param mixed $company (default: FALSE)
	 * @return Internet object
	 */
	public function getInternet($person_name = FALSE, $company = FALSE)
	{
		$internet = new stdclass();
		$internet->domain	= $this->getDomain($company);
		$internet->email	= $this->getEmail($person_name, $internet->domain);
		$internet->url		= $this->getUrl($internet->domain);	
		$internet->ip		= $this->getIp();	
		
		return $internet;
	}
	
	
	/**
	 * Generate a Person object with all relevent attributes.
	 * 
	 * @access public
	 * @param mixed $state_code (default: FALSE)
	 * @return void
	 */
	public function getPerson($state_code = FALSE)
	{
		$state_code = $state_code ? $state_code : $this->getState()->code;
		
		$person = new stdclass();

		$person->guid = $this->getGuid();
		$person->unique_hash = $this->getUniqueHash();
		
		$person->name = $this->getFullName(); // Returns an object with first, middle, last, and gender properties
		
		if (rand(1,100) % 5 == 0)
		{
			// Self employed?  Name the business after them.
			$person->company = $this->getCompanyName($person->name->last);
		}
		else
		{
			// Generate some random company name.
			$person->company = $this->getCompanyName();
		}

		# Primary address
		$person->address = $this->getAddress($state_code); // Returns object with line_1, line_2, city, zip, county, state->code, and state->name properties.
		
		# Secondary Address.  Mailing Address?  Use same zip code and primary address
		$person->address2 = $this->getAddress($state_code, $person->address->zip);
		
		$person->internet = $this->getInternet($person->name, $person->company);

		# Everyone has at least 2 or three phone numbers
		$person->phone	= new stdclass();
		$person->phone->home	= $this->getPhone($state_code, $person->address->zip);
		$person->phone->mobile	= $this->getPhone($state_code, $person->address->zip);
		$person->phone->work	= $this->getPhone($state_code, $person->address->zip);

		$person->ssn	= $this->getSsn();
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
		 $visaPrefixList[] =  "4539";
		 $visaPrefixList[] =  "4556";
		 $visaPrefixList[] =  "4916";
		 $visaPrefixList[] =  "4532";
		 $visaPrefixList[] =  "4929";
		 $visaPrefixList[] =  "40240071";
		 $visaPrefixList[] =  "4485";
		 $visaPrefixList[] =  "4716";
		 $visaPrefixList[] =  "4";
		
		 $mastercardPrefixList[] =  "51";
		 $mastercardPrefixList[] =  "52";
		 $mastercardPrefixList[] =  "53";
		 $mastercardPrefixList[] =  "54";
		 $mastercardPrefixList[] =  "55";
		
		 $amexPrefixList[] =  "34";
		 $amexPrefixList[] =  "37";
		
		 $discoverPrefixList[] = "6011";
		
		 $routingPrefixList[] = "01";
		 $routingPrefixList[] = "02";
		 $routingPrefixList[] = "03";
		 $routingPrefixList[] = "04";
		 $routingPrefixList[] = "05";
		 $routingPrefixList[] = "06";
		 $routingPrefixList[] = "07";
		 $routingPrefixList[] = "08";
		 $routingPrefixList[] = "09";
		 $routingPrefixList[] = "10";
		 $routingPrefixList[] = "11";
		 $routingPrefixList[] = "12";
		
		 switch($type)
		 {
			 case 'Visa':
			 	return $this->completedBankNumber($visaPrefixList, 16);
			 case 'Master Card':
			 	return $this->completedBankNumber($mastercardPrefixList, 16);
			 case 'American Express':
			 	return $this->completedBankNumber($amexPrefixList, 15);
			 case 'Discover':
			 	return $this->completedBankNumber($discoverPrefixList, 16);
			 case 'Routing':
			 	return $this->completedBankNumber($routingPrefixList, 9);
			 default:
			 	return $this->completedBankNumber($visaPrefixList, 16);
		}
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
	
		while ( strlen($ccnumber) < ($length - 1) )
		{
			$ccnumber .= rand(0,9);
		}
	
		# Calculate sum
	
		$sum = 0;
		$pos = 0;
	
		$reversedCCnumber = strrev( $ccnumber );
	
		while ( $pos < $length - 1 )
		{
	
			$odd = $reversedCCnumber[ $pos ] * 2;
	
			if ( $odd > 9 )
			{
				$odd -= 9;
			}
	
			$sum += $odd;
	
			if ( $pos != ($length - 2) )
			{
				$sum += $reversedCCnumber[ $pos +1 ];
			}
	
			$pos += 2;
		}
	
		# Calculate check digit
	
		$checkdigit = (( floor($sum/10) + 1) * 10 - $sum) % 10;
		$ccnumber .= $checkdigit;
	
		return $ccnumber;
	}

	/**
	 * Using PDO for database access to decrease framework dependence. 
	 */
	protected function query($sql, $params = array()){

		$db_type = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
		
		if ($db_type == 'sqlite')
		{
			$sql = str_ireplace('rand()', 'random()', $sql);
		}

		try
		{
			$sth = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$sth->setFetchMode(PDO::FETCH_OBJ);  
			$sth->execute($params);
			return $sth;
		}
		catch (Exception $e)
		{
			throw $e;
		}
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

