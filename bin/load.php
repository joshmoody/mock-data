<?php

namespace joshmoody\Mock\Bin;

use joshmoody\Mock\Models\Database;
use joshmoody\Mock\Models\LastName;
use joshmoody\Mock\Models\FirstName;
use joshmoody\Mock\Models\Street;
use joshmoody\Mock\Models\Zipcode;

require_once (dirname(__DIR__)) . '/vendor/autoload.php';

Database::init();

function get_filename($file)
{
	return sprintf('%s/data/%s', dirname(__DIR__), $file);
}

function load_lastnames($limit = 500)
{
	LastName::truncate();
	
	$fp = fopen(get_filename('lastnames.txt'), 'r');
	
	$count = 0;
	
	while (!feof($fp)) {

		if ($count > $limit - 1) {
			return $count;
		} else {
			$line = trim(fgets($fp));
	
			if (strlen($line) > 0) {
				$row = unpack("A14name/A7freq/A7cumulfreq/A6rank", trim($line));
		
				$lastname = new LastName();
				$lastname->name = ucfirst(strtolower(trim($row['name'])));
				$lastname->rank = ucfirst(strtolower(trim($row['rank'])));
	
				$lastname->save();
				
				$count++;
			} else {
				continue;
			}	
		}
	}
	
	fclose($fp);
	
	return $count;
}

function load_firstnames($limit = 500)
{
	FirstName::truncate();
	
	$loaded_female = load_female_firstnames($limit);
	$loaded_male = load_male_firstnames($limit);
	
	return $loaded_female + $loaded_male;
}

function load_female_firstnames($limit = 500)
{
	$fp = fopen(get_filename('female_firstnames.txt'), 'r');

	$count = 0;
	
	while (!feof($fp)) {

		if ($count > $limit - 1) {
			fclose($fp);
			return $count;
		} else {

			$line = trim(fgets($fp));

			if (strlen($line) > 0) {
				$row = unpack("A14name/A7freq/A7cumulfreq/A6rank", trim($line));

				$firstname = new FirstName();
				$firstname->name = ucfirst(strtolower(trim($row['name'])));
				$firstname->rank = ucfirst(strtolower(trim($row['rank'])));
				$firstname->gender = 'F';
				$firstname->save();
				
				$count++;
			}
		}
	}

	fclose($fp);
	return $count;
}

function load_male_firstnames($limit = 500)
{
	$fp = fopen(get_filename('male_firstnames.txt'), 'r');

	$count = 0;
	
	while (!feof($fp)) {

		if ($count > $limit - 1) {
			fclose($fp);
			return $count;
		} else {

			$line = trim(fgets($fp));

			if (strlen($line) > 0) {
				$row = unpack("A14name/A7freq/A7cumulfreq/A6rank", trim($line));

				$firstname = new FirstName();
				$firstname->name = ucfirst(strtolower(trim($row['name'])));
				$firstname->rank = ucfirst(strtolower(trim($row['rank'])));
				$firstname->gender = 'M';
				$firstname->save();
				
				$count++;
			}
		}
	}

	fclose($fp);
	return $count;
}

function load_streets()
{
	Street::truncate();
	
	$fp = fopen(get_filename('streets.txt'), 'r');
	
	$count = 0;

	while(!feof($fp)) {
		$name = trim(fgets($fp));
		
		if (strlen($name) > 0) {
			$street = new Street();
			
			$street->name = $name;
			$street->save();
			
			$count++;
		} else {
			continue;
		}
	}

	fclose($fp);
	
	return $count;
}

$loaded = load_lastnames();
print "Loaded $loaded last names\n";

$loaded = load_firstnames();
print "Loaded $loaded first names\n";

$loaded = load_streets();
print "Loaded $loaded streets\n";
