<?php

require __DIR__ . '/env.php';

// @todo Composer, incl self-load?
require WHERE_IS_HTTP . '/autoload.php';
require __DIR__ . '/lib/fuelly/autoload.php';

header('Content-type: text/plain; charset=utf-8');

use rdx\fuelly\Client;
use rdx\fuelly\WebAuth;

function getTestClient($mail, $pass, $session) {
	$client = new Client(new WebAuth($mail, $pass, $session));
	return $client;
}
