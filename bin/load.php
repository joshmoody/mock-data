<?php

namespace joshmoody\Mock\Bin;

use joshmoody\Mock\Models\Database;
use joshmoody\Mock\Models\LastName;
use joshmoody\Mock\Models\FirstName;
use joshmoody\Mock\Models\Street;
use joshmoody\Mock\Models\Zipcode;

use Illuminate\Database\Capsule\Manager as DB;

require_once (dirname(__DIR__)) . '/vendor/autoload.php';

#$opts = ['driver' => 'mysql', 'host' => 'localhost', 'database' => 'mock', 'username' => 'root', 'password' => 'root'];
$opts = ['driver' => 'sqlite'];
Database::init($opts);

// Disable query log so we don't run out of memory logging all these inserts.
DB::disableQueryLog();

function get_filename($file)
{
	return sprintf('%s/data/%s', dirname(__DIR__), $file);
}

function extract_datafiles()
{
	$archive = get_filename('data.zip');
	$extract_path = sprintf('%s/data', dirname(__DIR__));
	
	$zip = new \ZipArchive;
	$res = $zip->open($archive);
	
	if ($res === true) {
		$zip->extractTo($extract_path);
		$zip->close();
		printf("Extracted zip archive to %s \n", $extract_path);
	} else {
		printf("Extracting zip archive %s to %s failed with code %s \n", $archive, $extract_path, $res);
	}
}

function load_lastnames($limit = 500)
{

	if (!DB::schema()->hasTable('last_names'))
	{
		DB::schema()->create('last_names', function($table)
		{
			# Define some fields.
		    $table->increments('id');
		    $table->string('name', 15);
		    $table->integer('rank');
		    
		    # Add some indexes.
		    $table->index('rank');
		});
	} else {
		LastName::truncate();
	}
	
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
	if (!DB::schema()->hasTable('first_names'))
	{
		DB::schema()->create('first_names', function($table)
		{
			# Define the fields.
		    $table->increments('id');
		    $table->string('name', 15);
		    $table->string('gender', 1);
		    $table->integer('rank');
		    
		    # Add some indexes.
		    $table->index('gender');
		    $table->index('rank');
		});
	} else {
		FirstName::truncate();
	}
	
	
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
	if (!DB::schema()->hasTable('streets'))
	{
		DB::schema()->create('streets', function($table)
		{
			# Define some fields.
		    $table->increments('id');
		    $table->string('name', 50);
		});
	} else {
		Street::truncate();
	}
	
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

function load_zipcodes()
{
	DB::schema()->dropIfExists('zipcodes');
	
	DB::schema()->create('zipcodes', function($table)
	{
		# Define some fields.
	    $table->increments('id');
	    $table->string('zip', 10);
	    $table->string('type', 20);
	    $table->string('city', 50);
	    $table->text('acceptable_cities');
	    $table->text('unacceptable_cities');
	    $table->string('state_code', 2);
	    $table->string('state', 50);
	    $table->string('county', 50);
	    $table->string('timezone', 50);
	    $table->string('area_codes', 50);
	    $table->float('latitude');
	    $table->float('longitude');
	    $table->string('world_region', 50);
	    $table->string('country', 50);
	    $table->smallInteger('decomissioned');
	    $table->bigInteger('estimated_population');
	    $table->string('notes', 50);
	    
	    # Add some indexs.
	    $table->index('state_code');
	    $table->index('state');
	    $table->index('zip');
	    $table->index('county');
	});
	
	// The zip code database only contains state codes - no state names. The state abbreviations file supplements this data.
	
	$abbreviations = file(get_filename('state_abbreviations.txt'));
	$state_lookup = [];
	
	foreach ($abbreviations as $x) {
		$row = unpack("A32name/A2code", trim($x));
		$state_lookup[trim($row['code'])] = ucfirst(strtolower(trim($row['name'])));
	}
	
	$fp = fopen(get_filename('zip_code_database.csv'), 'r');
	
	$counter = 0; // Total number records processed.
	$loaded = 0; // Number records actually loaded.
	while(!feof($fp)) {
		
		$counter++;
		
		list(
			$zip,
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
			$notes
		) = fgetcsv($fp);

		// Skip heading row and everyting but standard zip codes for the 50 states and DC
		if ($counter > 1 && $type == 'STANDARD' && !in_array($state_code, ['GU','PR','VI'])) {

			$loaded++;
			
			if (array_key_exists($state_code, $state_lookup)) {
				$state = $state_lookup[$state_code];
			} else {
				$state = $state_code;
			}
			
			$zipcodes = new Zipcode();
			$zipcodes->zip = $zip;
			$zipcodes->type = $type;
			$zipcodes->city = ucwords($primary_city);
			$zipcodes->acceptable_cities = $acceptable_cities;
			$zipcodes->unacceptable_cities = $unacceptable_cities;
			$zipcodes->state_code = $state_code;
			$zipcodes->state = ucwords($state);
			$zipcodes->county = str_replace(' County', '', $county);
			$zipcodes->timezone = $timezone;
			$zipcodes->area_codes = $area_codes;
			$zipcodes->latitude = $lat;
			$zipcodes->longitude = $long;
			$zipcodes->world_region = $world_region;
			$zipcodes->country = $country;
			$zipcodes->decomissioned = $decomissioned;
			$zipcodes->estimated_population = $estimated_population;
			$zipcodes->notes = $notes;
			$zipcodes->save();
		}
	}
	
	return $loaded;
}

extract_datafiles();

$loaded = load_lastnames();
print "Loaded $loaded last names\n";

$loaded = load_firstnames();
print "Loaded $loaded first names\n";

$loaded = load_streets();
print "Loaded $loaded streets\n";

$loaded = load_zipcodes();
print "Loaded $loaded zipcodes\n";

DB::enableQueryLog();