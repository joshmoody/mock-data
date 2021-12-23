<?php

require __DIR__ . '/../vendor/autoload.php';

$loader = new \joshmoody\Mock\DataLoader;

//$db_path = $loader->getStoragePath('test-db.sqlite');
//$pdo = new PDO('sqlite:' . $db_path);
//$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//$loader->setPdo($pdo);

$loader->extractDataFiles();

$loaded = $loader->loadNames();
print "Loaded $loaded names\n";

$loaded = $loader->loadStreets();
print "Loaded $loaded streets\n";

$loaded = $loader->loadZipcodes();
print "Loaded $loaded zipcodes\n";
