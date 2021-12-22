<?php
namespace joshmoody\Mock\Tests\Feature;

require_once __DIR__ . '/../../vendor/autoload.php';

use joshmoody\Mock\Generator;

$generator = new Generator();

$person = $generator->getPerson('AR');

dump($person);
