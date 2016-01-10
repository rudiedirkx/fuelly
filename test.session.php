<?php

require 'bootstrap.php';

$client = getTestClient(FUELLY_TEST_MAIL, FUELLY_TEST_PASS, @$_GET['session']);

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
echo "To save this session, go to\n\n" . basename(__FILE__) . '?session=' . $client->auth->session;
