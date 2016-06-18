<?php

require __DIR__ . '/env.php';

require __DIR__ . '/vendor/autoload.php';

header('Content-type: text/plain; charset=utf-8');

use rdx\fuelly\Client;
use rdx\fuelly\WebAuth;

function getTestClient($mail, $pass, $session) {
	$auth = new WebAuth($mail, $pass, $session);
	$input = new InputConversion('km', 'l', 'kmpl', ',', '.');
	$client = new Client($auth, $input);
	return $client;
}
