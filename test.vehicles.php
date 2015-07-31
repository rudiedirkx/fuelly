<?php

require 'bootstrap.php';

$client = getTestClient(FUELLY_TEST_MAIL, FUELLY_TEST_PASS, @$_GET['session']);

$client->refreshSession();
echo $client->session . "\n\n\n";

echo "Vehicles:\n";
$vehicles = $client->vehicles;
print_r($vehicles);
echo "\n\n";

echo "Client:\n";
print_r($client);
echo "\n\n";
