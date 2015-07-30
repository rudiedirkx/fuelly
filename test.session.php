<?php

require 'bootstrap.php';

header('Content-type: text/plain; charset=utf-8');

use rdx\fuelly\Client;

$client = new Client;
$client->mail = FUELLY_TEST_MAIL;
$client->pass = FUELLY_TEST_PASS;
$client->session = @$_GET['session'];

echo "Client:\n";
print_r($client);
echo "\n\n";

echo "Refresh:\n";
var_dump($client->refreshSession());
echo "\n\n";

echo "Client:\n";
print_r($client);
echo "\n\n";

echo "\n\n";
echo "To save this session, go to\n\n" . basename(__FILE__) . '?session=' . $client->session;
