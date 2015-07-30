<?php

require 'bootstrap.php';

header('Content-type: text/plain; charset=utf-8');

use rdx\fuelly\Client;

$client = new Client;
$client->mail = FUELLY_TEST_MAIL;
$client->pass = FUELLY_TEST_PASS;
$client->session = @$_GET['session'];

$client->refreshSession();
echo $client->session . "\n\n\n";

echo "Vehicle:\n";
$vehicles = $client->vehicles;
$vehicle = $vehicles[ array_rand($vehicles) ];
print_r($vehicle);
echo "\n\n";

echo "Fuelling:\n";
$response = $client->addFuelUp(array(
	'usercar_id' => $vehicle['id'],
	'miles_last_fuelup' => 100,
	'amount' => 10,
));
if ( !empty($response->fuelup_id) ) {
	var_dump($response->fuelup_id);
}
else {
	print_r($response);
}
echo "\n\n";

echo "Client:\n";
print_r($client);
echo "\n\n";
