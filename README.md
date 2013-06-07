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

	$person = get_person($state_code);
	print_r($person);

Example output:
		
	stdClass Object
	(
	    [guid] => 6461aa1a-d837-31a4-a53e-209c53e4eb06
	    [unique_hash] => 8ce9020800900a341dd193ec046544ec8b8a4e41
	    [name] => stdClass Object
	        (
	            [first] => Franklin
	            [middle] => Jeffrey
	            [last] => Hayes
	            [gender] => M
	        )
	
	    [company] => Crawford Plumbing
	    [address] => stdClass Object
	        (
	            [line_1] => 1294 Edgewood Drive
	            [line_2] => 
	            [city] => Hardy
	            [zip] => 72542
	            [county] => Sharp
	            [state] => stdClass Object
	                (
	                    [code] => AR
	                    [name] => Arkansas
	                )
	
	        )
	
	    [address2] => stdClass Object
	        (
	            [line_1] => 7286 4th Street
	            [line_2] => 
	            [city] => Hardy
	            [zip] => 72542
	            [county] => Sharp
	            [state] => stdClass Object
	                (
	                    [code] => AR
	                    [name] => Arkansas
	                )
	
	        )
	
	    [internet] => stdClass Object
	        (
	            [domain] => crawfordplumbing.com
	            [email] => franklinhayes@yahoo.com
	            [url] => http://crawfordplumbing.com
	            [ip] => 102.121.87.79
	        )
	
	    [phone] => stdClass Object
	        (
	            [home] => 870-925-5123
	            [mobile] => 870-125-5868
	            [work] => 870-374-9335
	        )
	
	    [ssn] => 432281735
	    [dln] => stdClass Object
	        (
	            [number] => 943142245
	            [state] => stdClass Object
	                (
	                    [code] => AR
	                    [name] => Arkansas
	                )
	
	            [expiration] => 09/2013
	        )
	
	    [dob] => 1941-02-16
	    [credit_card] => stdClass Object
	        (
	            [type] => MasterCard
	            [number] => 4636465029552659
	            [expiration] => 05/2016
	        )
	
	    [bank_account] => stdClass Object
	        (
	            [type] => Checking
	            [name] => Arvest
	            [account] => 847986716
	            [routing] => 041249772
	        )
	
	)

Each type of data element above may be generated independently.  Examples:

	get_firstname($gender = NULL)
	get_lastname()
	get_full_name($gender = NULL)
	get_street()
	get_apartment()
	get_state()
	get_zip($state_code = FALSE)
	get_city($state_code = FALSE)
	get_address($state_code = FALSE)
	
Plus many more. See libraries/Mock_data.php for all available options.
	
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
- PHP 5 with MySQL PDO extension.

## Installation
Run the SQL statements from data/create\_tables.sql and data/load\_tables.sql to generate all the base data.

	mysql -u root -p -h localhost < data/create_tables.sql
	
	mysql -u root -p -h localhost < data/load_tables.sql
	
This library may be loaded as a CodeIgniter libray by copying config/mock.php to your application/config directory and libraries/Mock_data.php to your application/libraries directory.
Then load the library with:

	$this->load->config('mock', TRUE);
	$mock_config = $this->config->item('mock');
	$this->load->library('mock_data', $mock_config);
	$person = $this->mock_data->get_person('AR'); // Load a person from Arkansas.
	
	print_r($person);
	
This library uses straight PDO for database access and does not rely on the database abstraction of any particular framework. If using in another framework or as a stand-a-lone library:

	require_once 'config/mock.php';
	require_once 'libraries/Mock_data.php';
	
	$mock = new Mock_data($config);
	$person = $mock->get_person('AR');
	
	print_r($person);

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


## Installation via Composer
    composer require --dev joshmoody/mock-data-generator dev-master
    
https://packagist.org/packages/joshmoody/mock-data-generator

## License and copyright
Licensed under the BSD (Berkeley Software Distribution) License.
Copyright (c) 2013, Josh Moody. All rights reserved.

