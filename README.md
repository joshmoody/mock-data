# Mock Data Generator
Generate realistic test data.

[![Build Status](https://travis-ci.org/joshmoody/mock-data.png?branch=master)](https://travis-ci.org/joshmoody/mock-data)

## Why?
I work with very data-intensive applications. Sometimes I need large quantities of test data for building test cases and seeding web services, databases, online forms, etc.  I wrote this library to assist with this.

With a simple loop, I can generate a database representing 100,000 people to use in my development and testing.

## Base data sources included in this library
- US City/County/State/Zip Database
- First/Last Names from US Census
- Common U.S. Street Names

## Mock Data Generation
Many different types of mock data can be generated with this library.  From basic building blocks like numbers and dates to a Person with just about every attribute you need from a Date of Birth to Driver's License and Credit Card.

	<?php
	$generator = new joshmoody\Mock\Generator();
	
	$person = $generator->getPerson('AR');
	print_r($person);

Example output:

	stdClass Object
	(
	    [guid] => 83e55aea-0313-9c34-557c-642483c526d9
	    [unique_hash] => 6a396b5876dd1557f237a1871c31ba1244b10506
	    [name] => stdClass Object
	        (
	            [first] => Darren
	            [middle] => Jay
	            [last] => James
	            [gender] => M
	        )
	
	    [company] => Mason Furniture
	    [address] => stdClass Object
	        (
	            [line_1] => 7353 Main Street
	            [line_2] => 
	            [city] => Farmington
	            [zip] => 72730
	            [county] => Washington
	            [state] => stdClass Object
	                (
	                    [code] => AR
	                    [name] => Arkansas
	                )
	
	        )
	
	    [address2] => stdClass Object
	        (
	            [line_1] => 6817 Devon Court
	            [line_2] => Ste. 6337
	            [city] => Farmington
	            [zip] => 72730
	            [county] => Washington
	            [state] => stdClass Object
	                (
	                    [code] => AR
	                    [name] => Arkansas
	                )
	
	        )
	
	    [internet] => stdClass Object
	        (
	            [domain] => masonfurniture.biz
	            [username] => darrenjames
	            [email] => darren.james@gmail.com
	            [url] => http://masonfurniture.biz
	            [ip] => 164.173.80.196
	        )
	
	    [phone] => stdClass Object
	        (
	            [home] => 479-341-2117
	            [mobile] => 479-308-7757
	            [work] => 479-146-6536
	        )
	
	    [ssn] => 429326237
	    [dln] => stdClass Object
	        (
	            [number] => 969767857
	            [state] => stdClass Object
	                (
	                    [code] => AR
	                    [name] => Arkansas
	                )
	
	            [expiration] => 08/2015
	        )
	
	    [dob] => 1939-05-26
	    [credit_card] => stdClass Object
	        (
	            [type] => Visa
	            [number] => 4916441193760049
	            [expiration] => 04/2015
	        )
	
	    [bank_account] => stdClass Object
	        (
	            [type] => Checking
	            [name] => Regions
	            [account] => 396457523
	            [routing] => 076098730
	        )
	
	)

Each type of data element above may be generated independently.  Examples:

	getFirstName()
	getLastName()
	getFullName('M')
	getStreet()
	getApartment()
	getState()
	getZip('AR')
	getCity('AR')
	getAddress('AR')
	
Plus many more. See src/joshmoody/Mock/Generator.php for all available options.
	
## Data Realism
This library is designed to create very realistic-looking data.

- If generating a person:
	- If a state is specified:
		- The city will be a valid city in that state
		- The area code, zip, and county will be correct for that city
		- The SSN will be in a valid range for that state
	- The email address will contain some portion of their name
	- First/middle name will be appropriate for the selected gender
	- For credit card numbers:
		- The prefix and length will match the type of card generated (MasterCard, Visa, etc.)

## Random Data
In addition to realistic data generation, you can also use this library to easily pick a random value from an array.

	<?php
	$color = $generator->fromArray(['Red' , 'White', 'Blue']);

Or get a boolean.

	<?php
	$bool = $generator->getBool(); // Returns bool(true) or bool(false);

Or get a string representation of a boolean.

	<?php
	$yes_no = $generator->getBool('Yes', 'No'); // Returns string(Yes) or string(No);
	


## Requirements
- MySQL or SQlite
- PHP >= 5.4 with MySQL _or_ SQlite PDO extension.

## Installation

This library is distributed as a composer package.	
	
	$ composer require --dev joshmoody/mock-data dev-master

https://packagist.org/packages/joshmoody/mock-data

**You can use either SQLite or MySQL for storing the base mock data elements.**

## Zero Configuration Instructions - SQLite
The package ships with a sqlite database containing all the data needed for generating random records.

To use the default sqlite database, call the constructor without passing an options array.
	
	<?php
	$generator = new joshmoody\Mock\Generator();

## Easy Configuration Instructions - MySQL
Run the load script in bin/load.php and pass a dsn string (eg mysql://username:password@host/dbname) as the first parameter to the script.

	$ php bin/load.php mysql://root:root@localhost/mock

> NOTE: The database must already exist. The script will create the tables in that database.

Once the script has set up the database tables, you can pass in your dsn string when calling the constructor.

	<?php
	$generator = new joshmoody\Mock\Generator(['dsn' => 'mysql://root:root@localhost/mock']);

## Reloading Data
You can use the load script above to regenerate the sqlite database at any time. Run it the same as the MySQL Instructions above, but without any parameters.

	$ php bin/load.php

This may be useful if modifying the source data to better fit your needs.

## Acknowledgements

The geographic and demographic source data used in this library was derived from several places, including:

- <http://www.unitedstateszipcodes.org/zip-code-database/>
- <http://www.50states.com/tools/postal.htm>
- <https://www.census.gov/genealogy/www/data/1990surnames/names_files.html>
- <http://www.livingplaces.com/streets/most-popular_street_names.html>


## License and copyright
Licensed under the MIT License.
Copyright (c) 2013, Josh Moody. All rights reserved.

