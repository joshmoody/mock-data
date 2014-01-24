# Mock Data Generator
Generate realistic test data.

[![Build Status](https://travis-ci.org/joshmoody/mock-data.png?branch=master)](https://travis-ci.org/joshmoody/mock-data) [![Total Downloads](https://poser.pugx.org/joshmoody/mock-data/downloads.png)](https://packagist.org/packages/joshmoody/mock-data) [![Latest Stable Version](https://poser.pugx.org/joshmoody/mock-data/v/stable.png)](https://packagist.org/packages/joshmoody/mock-data)

## Why?
I work with very data-intensive applications. Sometimes I need large quantities of test data for building test cases and seeding web services, databases, online forms, etc.  I wrote this library to assist with this.

With a simple loop, I can generate a database representing 100,000 people to use in my development and testing.

## Base data sources included in this library
- US City/County/State/Zip Database
- First/Last Names from US Census
- Common U.S. Street Names

## Mock Data Generation
Many different types of mock data can be generated with this library.  From basic building blocks like numbers and dates to a Person with just about every attribute you need from a Date of Birth to Driver's License and Credit Card.

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

## Usage

``` php
$generator = new joshmoody\Mock\Generator();

$person = $generator->getPerson('AR');
print_r($person);
```

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

Each type of data element above may be generated independently.

### Names

Get a full name (first, middle, last, gender).

> Why is gender is included as a property of the name? In the U.S., first and middle names are usually closely associated with gender.

``` php
$name = $generator->getFullName();

/*
stdClass Object
(
    [first] => Laurie
    [middle] => Joyce
    [last] => Wilson
    [gender] => F
)
*/
```

Or get parts of a name:

``` php
$first = $generator->getFirstName('M'); // M=Male, F=Female, null = random.
/*
string(8) "Clarence"
*/

$middle = $generator->getMiddleName('M'); // M=Male, F=Female, null = random.
/*
string(4) "Dale"
*/

$last = $generator->getLastName();
/*
string(6) "Rogers"
*/
```

### Addresses

Get a full address with street, city, state, zip

``` php
$address = $generator->getAddress();

/*
stdClass Object
(
    [line_1] => 2835 Hamilton Street
    [line_2] => 
    [city] => Hyndman
    [zip] => 15545
    [county] => Bedford
    [state] => stdClass Object
        (
            [code] => PA
            [name] => Pennsylvania
        )

)
*/
```
	
Or gets parts of an address:

``` php
$street = $generator->getStreet();
/*
string(15) "2162 9th Street"
*/	

$apartment = $generator->getApartment();
/*
string(9) "Apt. 6677"
*/

$city = $generator->getCity('AR');
/*
string(8) "Little Rock"
*/

$state = $generator->getState();
/*
stdClass Object
(
    [code] => AR
    [name] => Arkansas
)
*/

$zip = $generator->getZip('AR');
/*
string(5) "72201"
*/
```

### Phone Numbers

```php
$phone = $generator->getPhone([$state_code = false, $zip = false, $include_toll_free = false]);
/*
string(12) "908-519-1084"
*/
```

### Internet

``` php
$internet = $generator->getInternet([$person_name = null, $company = null]);
/*
stdClass Object
(
    [domain] => martinez.us
    [username] => swilliams
    [email] => stacey.williams@gmail.com
    [url] => https://martinez.us
    [ip] => 157.116.10.90
)
*/

$domain = $generator->getDomain($domain = null);
/*
string(8) "dean.com"
*/

$username = $generator->getUsername([$person_name = null]);
/*
string(14) "pedro.thompson"
*/

$email = $generator->getEmail([$person_name = null, $domain = null]);
/*
string(20) "fred.harrison@me.com"
*/

$url = $generator->getUrl();
/*
string(19) "http://hernandez.us"
*/

$ip = $generator->getIp();
/*
string(13) "101.114.68.26"
*/
```

## Random Data
In addition to realistic data generation, you can also use this library to easily pick a random value from an array.

``` php
$color = $generator->fromArray(['Red' , 'White', 'Blue']);
/*
string(3) "Red"
*/
```
	
Or get a boolean.

``` php
$bool = $generator->getBool(); // Returns bool(true) or bool(false);
/*
bool(false)
*/
```

Or get a string representation of a boolean. You define the return values for true/false

``` php
$yes_no = $generator->getBool('Yes', 'No'); // Returns string(Yes) or string(No)
/*
string(3) "Yes"
*/

$aye_nay = $generator->getBool('Aye', 'Nay'); // returns string(Aye) or string (Nay)
/*
 string(3) "Nay"
*/
```

## Requirements
- MySQL or SQlite
- PHP >= 5.4 with MySQL _or_ SQlite PDO extension.

## Installation
Installation of this package is easy with Composer. If you aren't familiar with the Composer Dependency Manager for PHP, [you should read this first](https://getcomposer.org/doc/00-intro.md).

If you don't already have [Composer](https://getcomposer.org) installed (either globally or in your project), you can install it like this:

	$ curl -sS https://getcomposer.org/installer | php

Create a file named composer.json somewhere in your project with the following content:

``` json
{
    "require": {
        "joshmoody/mock-data": "dev-master"
    }
}
```

## Zero Configuration Instructions - SQLite
The package ships with a sqlite database containing all the data needed for generating random records.

To use the default sqlite database, call the constructor without passing an options array.
	
``` php
$generator = new joshmoody\Mock\Generator();
```

## Easy Configuration Instructions - MySQL
Run the load script in bin/load.php and pass a dsn string (eg mysql://username:password@host/dbname) as the first parameter to the script.

``` bash
$ php bin/load.php mysql://root:root@localhost/mock
```

> NOTE: The database must already exist. The script will create the tables in that database.

Once the script has set up the database tables, you can pass in your dsn string when calling the constructor.

``` php
$generator = new joshmoody\Mock\Generator(['dsn' => 'mysql://root:root@localhost/mock']);
```

## Reloading Data
You can use the load script above to regenerate the sqlite database at any time. Run it the same as the MySQL
Instructions above, but without any parameters. This may be useful if modifying the source data to better fit your needs.

``` bash
$ php bin/load.php
```

## Acknowledgements

The geographic and demographic source data used in this library was derived from several places, including:

- <http://www.unitedstateszipcodes.org/zip-code-database/>
- <http://www.50states.com/tools/postal.htm>
- <https://www.census.gov/genealogy/www/data/1990surnames/names_files.html>
- <http://www.livingplaces.com/streets/most-popular_street_names.html>


## License and copyright
Licensed under the MIT License.
Copyright (c) 2013, Josh Moody. All rights reserved.

