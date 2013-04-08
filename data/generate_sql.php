<?php

# Clear all mock tables to prep for loading data.
function clear_tables()
{
	printf("TRUNCATE TABLE `lastnames`;\n");
	printf("TRUNCATE TABLE `firstnames`;\n");
	printf("TRUNCATE TABLE `streets`;\n");	
	printf("TRUNCATE TABLE `zipcodes`;\n");
}

# Load last name file
function load_last_names()
{
	$fp = fopen('lastnames.txt', 'r');
	
	while(!feof($fp))
	{
		$line = trim(fgets($fp));
		if (strlen($line) > 0)
		{
			$row = unpack("A14name/A7freq/A7cumulfreq/A6rank", trim($line));
	
			$name = ucfirst(strtolower(trim($row['name'])));
			$rank = ucfirst(strtolower(trim($row['rank'])));
			printf('INSERT INTO `lastnames` (`name`, `rank`) VALUES ("%s","%s");', mysql_escape_string($name), mysql_escape_string($rank));
			print "\n";
		}
		else
		{
			continue;
		}
	}
	
	fclose($fp);
}

# Load female first name file
function load_female_names()
{
	$fp = fopen('female_firstnames.txt', 'r');
	
	while(!feof($fp))
	{
		$line = trim(fgets($fp));
	
		if (strlen($line) > 0)
		{
			$row = unpack("A14name/A7freq/A7cumulfreq/A6rank", trim($line));
	
			$name = ucfirst(strtolower(trim($row['name'])));
			$rank = ucfirst(strtolower(trim($row['rank'])));
			printf('INSERT INTO `firstnames` (`name`, `gender`, `rank`) VALUES ("%s","F","%s");', mysql_escape_string($name), mysql_escape_string($rank));
			print "\n";
		}
		else
		{
			continue;
		}
	}
	
	fclose($fp);
}

# Load male first name file
function load_male_names()
{
	$fp = fopen('male_firstnames.txt', 'r');
	
	while(!feof($fp))
	{
		$line = trim(fgets($fp));
	
		if (strlen($line) > 0)
		{
			$row = unpack("A14name/A7freq/A7cumulfreq/A6rank", trim($line));
	
			$name = ucfirst(strtolower(trim($row['name'])));
			$rank = ucfirst(strtolower(trim($row['rank'])));
			printf('INSERT INTO `firstnames` (`name`, `gender`, `rank`) VALUES ("%s","M","%s");', mysql_escape_string($name), mysql_escape_string($rank));
			print "\n";
		}
		else
		{
			continue;
		}
	}

	fclose($fp);
}

# Load street names
function load_streets()
{
	$fp = fopen('streets.txt', 'r');
	
	while(!feof($fp))
	{
		$name = trim(fgets($fp));
	
		if (strlen($name) > 0)
		{
			printf('INSERT INTO `streets` (`name`) VALUES ("%s");', mysql_escape_string($name));
			print "\n";
		}
		else
		{
			continue;
		}
	}

	fclose($fp);
}

# Load zip codes
function load_zipcodes()
{
	// The zip code database only contains state codes - no state names.  The state abbreviations file supplements this data.
	$abbreviations = file('state_abbreviations.txt');
	$state_lookup = array();
	
	foreach($abbreviations as $x)
	{
		$row = unpack("A32name/A2code", trim($x));
		$state_lookup[trim($row['code'])] = ucfirst(strtolower(trim($row['name'])));
	}
	
	$fp = fopen('zip_code_database.csv', 'r');
	
	$counter = 0;

	while(!feof($fp))
	{
		$counter++;
		
		list(	$zip,
				$type,
				$primary_city,
				$acceptable_cities,
				$unacceptable_cities,
				$state_code,
				$county,
				$timezone,
				$area_codes,
				$lat,
				$long,
				$world_region,
				$country,
				$decomissioned,
				$estimated_population,
				$notes) = fgetcsv($fp);

		if ($counter > 1 && $type == 'STANDARD') // Skip heading row and everyting but standard zip codes.
		{
			if (array_key_exists($state_code, $state_lookup))
			{
				$state = $state_lookup[$state_code];
			}
			else
			{
				$state = $state_code;
			}
			
			printf('INSERT INTO `zipcodes`( `zip`,
											`type`,
											`city`,
											`acceptable_cities`,
											`unacceptable_cities`,
											`state_code`,
											`state`,
											`county`,
											`timezone`,
											`area_codes`,
											`latitude`,
											`longitude`,
											`world_region`,
											`country`,
											`decomissioned`,
											`estimated_population`,
											`notes`)
								values	   ("%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s");',
											mysql_escape_string($zip),
											mysql_escape_string($type),
											mysql_escape_string($primary_city),
											mysql_escape_string($acceptable_cities),
											mysql_escape_string($unacceptable_cities),
											mysql_escape_string($state_code),
											mysql_escape_string($state),
											mysql_escape_string(str_replace(' County', '', $county)),
											mysql_escape_string($timezone),
											mysql_escape_string($area_codes),
											mysql_escape_string($lat),
											mysql_escape_string($long),
											mysql_escape_string($world_region),
											mysql_escape_string($country),
											mysql_escape_string($decomissioned),
											mysql_escape_string($estimated_population),
											mysql_escape_string($notes));
													
			print "\n";
			
		}
	}
}
clear_tables();
load_last_names();
load_female_names();
load_male_names();
load_streets();
load_zipcodes();

