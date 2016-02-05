<?php

require 'bootstrap.php';

$client = getTestClient(FUELLY_TEST_MAIL, FUELLY_TEST_PASS, @$_GET['session']);

$client->refreshSession();
echo $client->auth->session . "\n\n\n";

echo "Vehicle:\n";
$vehicles = $client->vehicles;
$vehicle = $vehicles[ array_rand($vehicles) ];
print_r($vehicle);
echo "\n\n";

echo "Fuelling:\n";
$response = $client->addFuelUp(array(
	'usercar_id' => $vehicle->id,
	'miles_last_fuelup' => 100,
	'amount' => 10,
	'note' => 'TEST TEST TEST',
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
