<?php

require __DIR__ . '/env.php';
require WHERE_HTTP_AT . '/HTTP.php';

// This could be autoloaded...
require __DIR__ . '/lib/fuelly/src/Client.php';

header('Content-type: text/plain; charset=utf-8');

use rdx\fuelly\Client;

function getTestClient($mail, $pass, $session) {
	$client = new Client;
	$client->mail = $mail;
	$client->pass = $pass;
	$client->session = $session;
	return $client;
}
