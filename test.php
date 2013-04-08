<?php
require_once 'config/mock.php';
require_once 'libraries/Mock_data.php';

$mock = new Mock_data($config);
$person = $mock->get_person('AR');


print_r($person);
