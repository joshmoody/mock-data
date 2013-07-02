# Mock Data Generator
Generate realistic test data.

## Why?
I work with very data-intensive applications. Sometimes I need large quantities of test data for building test cases and seeding web services, databases, online forms, etc.  I wrote this library to assist with this.

With a simple loop, I can generate a database representing 100,000 people to use in my development and testing.

## Base data sources included in this library
- US City/County/State/Zip Database
- First/Last Names from US Census
- Common U.S. Street Names

## Mock Data Generation
Many different types of mock data can be generated with this library.  From basic building blocks like numbers and dates to a Person with just about every attribute you need from a Date of Birth to Driver's License and Credit Card.

	$opts = array('hostname' => 'localhost', 'username' => 'root', 'password' => 'root', 'database' => 'mock_data', 'db_driver' => 'mysql');
	$generator = new joshmoody\Mock\Generator($opts);
	
	$person = $generator->getPerson();
	print_r($person);

Example output:

	stdClass Object	
	(
	    [guid] => ecf66465-9a97-d134-79ad-f2a139437a95
	    [unique_hash] => 35e4ac33b1a89fde70f7ef0254e835282a531a88
	    [name] => stdClass Object
	        (
	            [first] => Edgar
	            [middle] => Charles
	            [last] => Fowler
	            [gender] => M
	        )
	
	    [company] => Fowler Realty
	    [address] => stdClass Object
	        (
	            [line_1] => 3217 Madison Court
	            [line_2] => Apartment W
	            [city] => Poteau
	            [zip] => 74953
	            [county] => Le Flore
	            [state] => stdClass Object
	                (
	                    [code] => OK
	                    [name] => Oklahoma
	                )
	
	        )
	
	    [address2] => stdClass Object
	        (
	            [line_1] => 9552 12th Street
	            [line_2] => Apt. 3693
	            [city] => Poteau
	            [zip] => 74953
	            [county] => Le Flore
	            [state] => stdClass Object
	                (
	                    [code] => OK
	                    [name] => Oklahoma
	                )
	
	        )
	
	    [internet] => stdClass Object
	        (
	            [domain] => fowlerrealty.net
	            [email] => edgar.fowler@fowlerrealty.net
	            [url] => https://www.fowlerrealty.net
	            [ip] => 0.144.51.92
	        )
	
	    [phone] => stdClass Object
	        (
	            [home] => 918-758-7706
	            [mobile] => 918-837-4856
	            [work] => 918-885-2146
	        )
	
	    [ssn] => 431835640
	    [dln] => stdClass Object
	        (
	            [number] => 963852749
	            [state] => stdClass Object
	                (
	                    [code] => OK
	                    [name] => Oklahoma
	                )
	
	            [expiration] => 08/2013
	        )
	
	    [dob] => 1963-08-25
	    [credit_card] => stdClass Object
	        (
	            [type] => Visa
	            [number] => 4024007171997611
	            [expiration] => 08/2016
	        )
	
	    [bank_account] => stdClass Object
	        (
	            [type] => Checking
	            [name] => Wells Fargo
	            [account] => 192757996
	            [routing] => 117062752
	        )
	
	)

Each type of data element above may be generated independently.  Examples:

	getFirstName($gender = NULL)
	getLastName()
	getFullName($gender = NULL)
	getStreet()
	getApartment()
	getState()
	getZip($state_code = FALSE)
	getCity($state_code = FALSE)
	getAddress($state_code = FALSE)
	
Plus many more. See src/joshmoody/Mock/Generator.php for all available options.
	
## Data Realism
This library is designed to create very realistic-looking data.

- If generating a person:
	- If a state is specified:
		- The city will be a valid city in that state
		- The area code, zip, and county will be correct for that city
	- The email address will contain some portion of their name
	- First/middle name will be appropriate for the selected gender
	- For credit card numbers:
		- The prefix and length will match the type of card generated (MasterCard, Visa, etc.)

## Requirements
- MySQL
- PHP >= 5.3.3 with MySQL PDO extension.

## Installation
Run the SQL statements from data/create\_tables.sql and data/load\_tables.sql to generate all the base data.

	mysql -u root -p -h localhost < data/create_tables.sql
	
	mysql -u root -p -h localhost < data/load_tables.sql
	

This library is distributed as a composer package.	
	composer require --dev joshmoody/mock-data-generator dev-master

https://packagist.org/packages/joshmoody/mock-data-generator
 
## Reloading Data
You can regenerate the database from the source data files.
You should only need to do this if modifying the source data to refresh the database.  Otherwise, just use the installation steps outlined above.
All commands should be executed from the data directory.

Parse all the source data files and construct SQL to insert into the database.

	php generate_sql.php > /tmp/mock_data.sql

Alter this statement as needed to match your username and database name

	mysql -u root -p -h localhost mock_data < /tmp/mock_data.sql


## Acknowledgements

The geographic and demographic source data used in this library was derived from several places, including:

- http://www.unitedstateszipcodes.org/zip-code-database/
- http://www.50states.com/tools/postal.htm
- https://www.census.gov/genealogy/www/data/1990surnames/names_files.html
- http://www.livingplaces.com/streets/most-popular_street_names.html


## License and copyright
Licensed under the BSD (Berkeley Software Distribution) License.
Copyright (c) 2013, Josh Moody. All rights reserved.

