<?php

require 'bootstrap.php';

header('Content-type: text/plain; charset=utf-8');

use rdx\fuelly\Client;

$client = new Client;
$client->mail = FUELLY_TEST_MAIL;
$client->pass = FUELLY_TEST_PASS;
$client->session = @$_GET['session'];

$client->refreshSession();

echo "Vehicles:\n";
$vehicles = $client->getVehicles();
print_r($vehicles);
echo "\n\n";

echo "Client:\n";
print_r($client);
echo "\n\n";
