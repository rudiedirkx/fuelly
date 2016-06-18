<?php

require 'bootstrap.php';

$client = getTestClient(FUELLY_TEST_MAIL, FUELLY_TEST_PASS, @$_GET['session']);

$client->ensureSession();
echo $client->auth->session . "\n\n\n";

echo "Vehicle:\n";
$vehicles = $client->vehicles;
$vehicle = $vehicles[ array_rand($vehicles) ];
print_r($vehicle);
echo "\n\n";

echo "Fuel-ups (scrape):\n";
$fuelups = $client->getFuelUpsWithIds($vehicle['id'], 50);
print_r($fuelups);
echo "\n\n";

echo "Fuel-up (scrape):\n";
// $fuelup = $fuelups[ array_rand($fuelups) ];
$fuelup = $fuelups[0];
print_r($fuelup);
echo "\n\n";

echo "Fuel-up (form):\n";
$fuelup = $client->getFuelUp($fuelup['id']);
print_r($fuelup);
echo "\n\n";

echo "Update (add 'x'):\n";
$fuelup['note'] .= 'x';
$fuelup['usercar_id'] = $vehicle['id'];
$saved = $client->updateFuelUp($fuelup['id'], $fuelup);
var_dump($saved);
echo "\n\n";
