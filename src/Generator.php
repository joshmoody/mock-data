<?php

namespace joshmoody\Mock;

use DateTime;
use Exception;
use joshmoody\Mock\Entities\Address;
use joshmoody\Mock\Entities\BankAccount;
use joshmoody\Mock\Entities\CreditCard;
use joshmoody\Mock\Entities\DriverLicense;
use joshmoody\Mock\Entities\FullName;
use joshmoody\Mock\Entities\Internet;
use joshmoody\Mock\Entities\Person;
use joshmoody\Mock\Entities\State;
use PDO;
use Simpl\SQL;
use StdClass;

class Generator
{

	/**
	 * @var SQL
	 */
	protected $sql;

	/**
	 * Generator constructor.
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->getDatabaseConnection();
	}

	/**
	 * @throws Exception
	 */
	public function getDatabaseConnection(): SQL
	{
		if (empty($this->sql)) {
			$dsn = [
				'prefix' => 'sqlite',
				'path' => __DIR__ . '/../data/database.sqlite'
			];

			$this->sql = new SQL($dsn);
		}

		return $this->sql;
	}

	/**
	 * Generate a float number between $min and $max, with precision $precision
	 *
	 * @param int $min
	 * @param int $max
	 * @param int $precision
	 * @return float
	 */
	public function getFloat($min = 0, $max = 10000, $precision = 2): float
	{
		$num = rand($min, $max) . '.' . $this->getString('number', $precision);

		if ($num > $max) {
			// In case adding the additional decimal points makes us exceed the max.
			$num = $max;
		}

		return round($num, $precision);
	}

	/**
	 *    Generate random string.
	 *
	 * @access public
	 * @param string $type Options: letter, number, or mix.     default: letter
	 * @param mixed $desired_length Will be random length if not specified.
	 * @return string
	 */
	public function getString($type = 'letter', $desired_length = null): string
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
	 * Generate a random number between $min and $max
	 *
	 * @param int $min
	 * @param int $max
	 * @return int
	 */
	public function getInteger($min = 0, $max = 10000): int
	{
		return rand($min, $max);
	}

	/**
	 * Returns a random character, A-Z
	 * @return string
	 */
	public function getLetter(): string
	{
		$letters = [
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z'
		];
		return $this->fromArray($letters);
	}

	/**
	 * Return a random value from an array
	 *
	 * @param array $array
	 * @return mixed
	 */
	public function fromArray($array = [])
	{
		if (is_array($array) && count($array) > 0) {
			return $array[rand(0, count($array) - 1)];
		} else {
			return null;
		}
	}

	/**
	 * Generate a unique sha1 hash.
	 *
	 * @return string
	 */
	public function getUniqueHash(): string
	{
		return sha1(uniqid(uniqid(), true));
	}

	/**
	 * @param null $state_code
	 * @return mixed
	 */
	public function getZip($state_code = null)
	{
		if (!empty($state_code)) {
			$sql = "SELECT * FROM zipcodes WHERE state_code = ? ORDER BY random()";
		} else {
			$sql = "SELECT * FROM zipcodes ORDER BY random()";
		}

		return $this->sql->query($sql, $state_code)->fetch(PDO::FETCH_OBJ)->zip;
	}

	/**
	 * @param bool $state_code
	 * @return mixed
	 */
	public function getCity($state_code = false)
	{
		if ($state_code) {
			$sql = "SELECT * FROM zipcodes WHERE state_code = ? ORDER BY random()";
		} else {
			$sql = "SELECT * FROM zipcodes ORDER BY random()";
		}

		return $this->sql->query($sql, $state_code)->fetch(PDO::FETCH_OBJ)->city;
	}

	/**
	 * Generate a Person object with all relevant attributes.
	 *
	 * @access public
	 * @param mixed $state_code (default: null)
	 * @return Person
	 */
	public function getPerson($state_code = null): Person
	{
		$state_code = !empty($state_code) ? $state_code : $this->getState()->code;

		$person = new Person;

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
		$person->phone = new stdclass();
		$person->phone->home = $this->getPhone($state_code, $person->address->zip);
		$person->phone->mobile = $this->getPhone($state_code, $person->address->zip);
		$person->phone->work = $this->getPhone($state_code, $person->address->zip);

		$person->ssn = $this->getSsn($state_code);
		$person->dln = $this->getDln($state_code);

		$person->dob = $this->getBirthDate();

		# Payment Implements
		$person->credit_card = $this->getCreditCard();
		$person->bank_account = $this->getBank();

		return $person;
	}

	/**
	 * Return a state
	 *
	 * @param null $state_code
	 * @return State
	 */
	public function getState($state_code = null): State
	{
		if (!empty($state_code)) {
			$sql = "SELECT * FROM zipcodes WHERE state_code = ? ORDER BY random()";
		} else {
			$sql = "SELECT * FROM zipcodes ORDER BY random()";
		}

		$res = $this->sql->query($sql, $state_code)->fetch(PDO::FETCH_OBJ);

		$State = new State;
		$State->code = $res->state_code;
		$State->name = $res->state;
		return $State;
	}

	/**
	 * Generate a GUID.
	 *
	 * @return string
	 */
	public function getGuid(): string
	{
		return sprintf(
			'%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
			mt_rand(0, 65535),
			mt_rand(0, 65535),    // 32 bits for "time_low"
			mt_rand(0, 65535),    // 16 bits for "time_mid"
			mt_rand(0, 4095),    // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
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
	 * Returns a Full Name
	 *
	 * @param string $gender .  Will be used to make sure both First and Middle Name are for same gender.
	 * @return FullName Gender included to avoid "A Boy Named Sue".
	 */
	public function getFullName($gender = null): FullName
	{
		if (empty($gender)) {
			$gender = $this->getGender();
		}

		$person_name = new FullName;
		$person_name->first = $this->getFirstName($gender);
		$person_name->middle = $this->getMiddleName($gender);
		$person_name->last = $this->getLastName();
		$person_name->gender = $gender;

		return $person_name;
	}

	/**
	 * Returns Gender (M/F) at random.
	 *
	 * @return string $gender
	 */
	public function getGender(): string
	{
		return $this->fromArray(['F', 'M']);
	}

	/**
	 * Generate a First Name
	 *
	 * Uses US Census data to get 250 most popular names for both male and female
	 *
	 * @param string $gender Do you want a male or female name? (M/F).    If null, selects a gender at random.
	 */
	public function getFirstName($gender = null, $rank = 250)
	{
		$gender = !empty($gender) ? $gender : $this->getGender();

		$sql = "SELECT * FROM first_names WHERE gender = ? AND rank <= ? ORDER BY random()";
		return $this->sql->query($sql, [$gender, $rank])->fetch(PDO::FETCH_OBJ)->name;
	}

	/**
	 * Alias for get_firstname()
	 *
	 * @param string $gender Do you want a male or female name? (M/F).    If null, selects a gender at random.
	 * @return string
	 */
	public function getMiddleName($gender = null): string
	{
		return $this->getFirstName($gender);
	}

	/**
	 * Generate a Last Name
	 *
	 * Uses US Census data to get $max most popular names for both male and female and selects one at random
	 *
	 * Pool is only 250 most frequent last names.  Increase by passing a higher value for $max
	 *
	 * @param int $max How large should our pool of names be? Default: 250
	 * @return string Last Name
	 */
	public function getLastName($max = 250): string
	{
		$sql = "SELECT * FROM last_names WHERE rank <= ? ORDER BY random()";
		return $this->sql->query($sql, $max)->fetch(PDO::FETCH_OBJ)->name;
	}

	/**
	 * Return a Company Name.  Uses a random last name plus a suffix that looks like a company name.
	 * You can optionally pass a name to serve as the prefix
	 *
	 * @param null $base_name
	 * @return string
	 */
	public function getCompanyName($base_name = null): string
	{
		$suffixes = [
			'Corporation',
			'Company',
			'Company, Limited',
			'Computer Repair',
			'Incorporated',
			'and Sons',
			'Group',
			'Group, PLC',
			'Furniture',
			'Flowers',
			'Sales',
			'Systems',
			'Tire',
			'Auto',
			'Plumbing',
			'Roofing',
			'Realty',
			'Foods',
			'Books'
		];

		if (empty($base_name)) {
			$base_name = $this->getLastName();
		}

		return $base_name . ' ' . $this->fromArray($suffixes);
	}

	/**
	 * Return object with full street info
	 *
	 * @param null $state_code
	 * @param null $zip
	 * @return Address
	 */
	public function getAddress($state_code = null, $zip = null): Address
	{
		$address = new Address;

		if (!empty($zip) && !empty($state_code)) {
			$sql = "SELECT * FROM zipcodes WHERE zip = :zip AND state_code = :state_code ORDER BY random()";
			$query = $this->sql->query(
				$sql,
				[
					'state_code' => $state_code,
					'zip' => $zip
				]
			);
		} elseif (!empty($zip)) {
			$sql = "SELECT * FROM zipcodes WHERE zip = :zip ORDER BY random()";
			$query = $this->sql->query(
				$sql,
				[
					'zip' => $zip
				]
			);
		} elseif (!empty($state_code)) {
			$sql = "SELECT * FROM zipcodes WHERE state_code = :state_code ORDER BY random()";
			$query = $this->sql->query(
				$sql,
				[
					'state_code' => $state_code,
				]
			);
		} else {
			$sql = "SELECT * FROM zipcodes ORDER BY random()";
			$query = $this->sql->query($sql);
		}

		$result = $query->fetch(PDO::FETCH_OBJ);

		$address->line_1 = $this->getStreet();

		if ($this->getBool()) {
			$address->line_2 = $this->getApartment();
		} else {
			$address->line_2 = null;
		}

		$address->city = $result->city;
		$address->zip = $result->zip;
		$address->county = $result->county;

		$address->state = new State;
		$address->state->code = $result->state_code;
		$address->state->name = $result->state;

		return $address;
	}

	/**
	 * Return a street name
	 *
	 * @return string
	 */
	public function getStreet(): string
	{
		$number = rand(100, 9999);

		$sql = "SELECT * FROM streets ORDER BY random()";
		$street_name = $this->sql->query($sql)->fetch(PDO::FETCH_OBJ)->name;
		return $number . ' ' . $street_name;
	}

	/**
	 * Generate a boolean. Use the parameters to custom the response to be true/false, 1/0, Yes/No, etc.
	 *
	 * @access public
	 * @param mixed $true Value that should be returned if true (default: true)
	 * @param mixed $false Value that should be returned if false (default: false)
	 * @param mixed $likely How likely is it (1-10) the result will be true?  1 = Always, 10 = Almost never.
	 * @return mixed the result.
	 *
	 * @codeCoverageIgnore
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
	 * Get an apartment number.
	 *
	 * @return string
	 */
	public function getApartment(): string
	{
		$types = ['Apt.', 'Apartment', 'Ste.', 'Suite', 'Box'];

		if ($this->getBool()) {
			$extra = $this->getLetter();
		} else {
			$extra = $this->getInteger(1, 9999);
		}

		$type = $this->fromArray($types);
		return $type . ' ' . $extra;
	}

	/**
	 * Generate internet information.  Domain, Email, URL, IP Address, Username
	 *
	 * @access public
	 * @param mixed $person_name (default: null)
	 * @param mixed $company (default: null)
	 * @return Internet
	 */
	public function getInternet($person_name = null, $company = null): Internet
	{
		if (empty($person_name)) {
			$person_name = $this->getFullName();
		}

		$internet = new Internet();
		$internet->domain = $this->getDomain($company);
		$internet->username = $this->getUserName($person_name);
		$internet->email = $this->getEmail($person_name, $internet->domain);
		$internet->url = $this->getUrl($internet->domain);
		$internet->ip = $this->getIp();

		return $internet;
	}

	/**
	 * @param null $base
	 * @return string
	 */
	public function getDomain($base = null): string
	{
		$domain = !empty($base) ? $base : $this->getLastName();

		$domain = preg_replace('/[^0-9a-z_A-Z]/', '', $domain);

		$tld = ['.com', '.net', '.us', '.biz'];
		return strtolower($domain) . $this->fromArray($tld);
	}

	/**
	 * @param null $person_name
	 * @return string
	 */
	public function getUsername($person_name = null): string
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
	 *
	 * @param null $person_name
	 * @param null $domain
	 * @return string
	 */
	public function getEmail($person_name = null, $domain = null): string
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
	 * @param null $domain
	 * @return string
	 */
	public function getUrl($domain = null): string
	{
		$protocol = ['https://www.', 'http://www.', 'http://', 'https://'];

		$domain = !empty($domain) ? $domain : $this->getDomain();

		return $this->fromArray($protocol) . $domain;
	}

	/**
	 * Get something that looks like an IP Address
	 * @return string
	 */
	public function getIp(): string
	{
		$parts = [];

		for ($i = 0; $i < 4; $i++) {
			$parts[] = $this->getInteger(0, 255);
		}

		return join('.', $parts);
	}

	/**
	 * Return a phone number
	 *
	 * @param null $state_code
	 * @param null $zip
	 * @param bool $include_toll_free
	 * @return string
	 */
	public function getPhone($state_code = null, $zip = null, $include_toll_free = false): string
	{
		if (!empty($zip)) {
			$sql = "SELECT * FROM zipcodes WHERE zip = :zip ORDER BY random()";
			$areacodes = $this->sql->query($sql, $zip)->fetch(PDO::FETCH_OBJ)->area_codes;
		} else {
			// Get a random state if state not provided
			$state_code = !empty($state_code) ? $state_code : $this->getState()->code;

			// Get area codes appropriate for this state
			$sql = "SELECT * FROM zipcodes WHERE state_code = :state_code ORDER BY random()";
			$areacodes = $this->sql->query($sql, $state_code)->fetch(PDO::FETCH_OBJ)->area_codes;
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
		$areacode = $this->fromArray($code_list);
		$prefix = rand(100, 999);
		$number = rand(1, 9999);

		return $areacode . '-' . $prefix . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
	}

	/**
	 * Generate a 9 digit number that could be an SSN for a given state.
	 * SSN Prefixes from http://socialsecuritynumerology.com/prefixes.php for
	 *
	 * @param null $state_code
	 * @return string
	 */
	public function getSsn($state_code = null): string
	{
		if (empty($state_code)) {
			$state_code = $this->getState()->code;
		}

		/**
		 * Prefixes 580-xx-xxxx and up are allocated to US Territories and other states.
		 * The below array structure does not support multiple prefix ranges, but this will do for now.
		 * We are looking for FAKE data, not COMPLETE data.
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
	 * Returns a DLN object that contains a driver license number, state, and expiration
	 *
	 * @param null $state_code
	 * @param int $min
	 * @param int $max
	 * @return DriverLicense
	 */
	public function getDln($state_code = null, $min = 900000001, $max = 999999999): DriverLicense
	{
		$dln = new DriverLicense();

		$dln->number = rand($min, $max);
		$dln->state = !empty($state_code) ? $state_code : $this->getState();
		$dln->expiration = $this->getExpiration();

		return $dln;
	}

	/**
	 * Get a future date. Suitable for DLN / CC Expiration
	 *
	 * @param string $format
	 * @return string formatted date string.
	 */
	public function getExpiration($format = 'm/Y'): string
	{
		$date_params = ['min_year' => date('Y'), 'max_year' => date('Y') + 3];
		return $this->getDate($date_params, $format);
	}

	/**
	 * Generate a random date.
	 * @param array $params Associative array with following keys: minYear, maxYear, minMonth, maxMonth
	 * @param string $format date() format for return value.  Default: Y-m-d
	 * @return string formatted date string.
	 */
	public function getDate($params = [], $format = 'Y-m-d'): string
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
		$rand_year = rand($min_year, $max_year);
		$rand_month = rand($min_month, $max_month);

		// Create a date object using the first day of this random month/year.
		$date = DateTime::createFromFormat('Y-m-d', join('-', [$rand_year, $rand_month, '01']));

		// How many days in this random month?
		$days_in_month = $date->format('t');

		// Pick a day of the month.
		$rand_day = rand(1, $days_in_month);

		return DateTime::createFromFormat('Y-m-d', join('-', [$rand_year, $rand_month, $rand_day]))->format($format);
	}

	/**
	 * Generate a reasonable birth date.     Default Age: 20-80 years.
	 *
	 * @param array $params Associative array with following keys: minYear, maxYear, minMonth, maxMonth
	 * @param string $format date() format for return value.  Default: Y-m-d
	 * @return string formatted date string.
	 */
	public function getBirthDate($params = [], $format = 'Y-m-d'): string
	{
		$params['min_year'] = array_key_exists('min_year', $params) ? $params['min_year'] : date('Y') - 80;
		$params['max_year'] = array_key_exists('max_year', $params) ? $params['max_year'] : date('Y') - 20;
		return $this->getDate($params, $format);
	}

	/**
	 * Generate a credit card number.
	 *
	 * @access public
	 * @param mixed $weighted (default: true) - Make it more likely to return MasterCard or Visa
	 * @return CreditCard
	 */
	public function getCreditCard($weighted = true): CreditCard
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

				for ($i = 0; $i < $count; $i++) {
					$card_types[] = $type;
				}
			}
		} else {
			$card_types = ['American Express', 'Discover', 'MasterCard', 'Visa'];
		}

		$cc = new CreditCard;

		$cc->type = $this->fromArray($card_types);

		// Get a random card number appropriate for this type that passes Luhn/Mod10 check
		$cc->number = $this->getBankNumber($cc->type);

		// Get an expiration date
		$cc->expiration = $this->getExpiration();

		return $cc;
	}

	/**
	 * I've adapted a credit card / routing number generator to meet my needs here. Original copyright below.
	 * Numbers created here will pass the Luhn Mod-10 test.
	 *
	 * @param string $type
	 * @return string
	 */
	public function getBankNumber($type = 'Visa'): string
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
	 * @param array $prefixlist The start of the CC number as a string, any number of digits.
	 * @param int $length Length of the CC number to generate. Typically 13 or 16
	 * @return string
	 */
	protected function completedBankNumber(array $prefixlist, int $length): string
	{
		$prefix = $prefixlist[rand(0, count($prefixlist) - 1)];
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
			$odd = $reversedCCnumber[$pos] * 2;

			if ($odd > 9) {
				$odd -= 9;
			}

			$sum += $odd;

			if ($pos != ($length - 2)) {
				$sum += $reversedCCnumber[$pos + 1];
			}

			$pos += 2;
		}

		# Calculate check digit

		$checkdigit = ((floor($sum / 10) + 1) * 10 - $sum) % 10;
		$ccnumber .= $checkdigit;

		return $ccnumber;
	}

	/**
	 * Generate bank account information.
	 *
	 * @access public
	 * @return BankAccount
	 */
	public function getBank(): BankAccount
	{
		$bank_account = new BankAccount;

		$bank_account->type = $this->fromArray(['Checking', 'Savings']);
		$bank_account->name = $this->fromArray(['First National', 'Arvest', 'Regions', 'Metropolitan', 'Wells Fargo']);

		$bank_account->account = $this->getInteger('1000', '999999999');
		$bank_account->routing = $this->getBankNumber('Routing');

		return $bank_account;
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
