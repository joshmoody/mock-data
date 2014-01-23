<?php

require_once 'vendor/autoload.php';

$generator = new joshmoody\Mock\Generator();  

// Person
$person = $generator->getPerson('AR');  
print_r($person);

// Person's Full Name
$name = $generator->getFullName();
print_r($name);

// Parts of a person's name
$first = $generator->getFirstName('M');
var_dump($first);

$middle = $generator->getMiddleName('M');
var_dump($middle);

$last = $generator->getLastName();
var_dump($last);

// Full Address
$address = $generator->getAddress();
print_r($address);

// Parts of an address
$street = $generator->getStreet();
var_dump($street);

$apartment = $generator->getApartment();
var_dump($apartment);

$state = $generator->getState();
print_r($state);

$city = $generator->getCity($state->code);
var_dump($city);

$zip = $generator->getZip($state->code);
var_dump($zip);

// Random from array
$color = $generator->fromArray(['Red' , 'White', 'Blue']);
var_dump($color);


// Boolean
$bool = $generator->getBool();
var_dump($bool);

$yes_no = $generator->getBool('Yes', 'No');
var_dump($yes_no);

$aye_nay = $generator->getBool('Aye', 'Nay');
var_dump($aye_nay);

// Phone Number
$phone = $generator->getPhone();
var_dump($phone);

// Internet
$internet = $generator->getInternet();
print_r($internet);

$domain = $generator->getDomain();
var_dump($domain);

$username = $generator->getUsername();
var_dump($username);

$email = $generator->getEmail();
var_dump($email);

$url = $generator->getUrl();
var_dump($url);

$ip = $generator->getIp();
var_dump($ip);
