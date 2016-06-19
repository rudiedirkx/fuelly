<?php

namespace rdx\fuelly;

use rdx\fuelly\Client;
use rdx\fuelly\FuelUp;

class Vehicle {

	public $client; // rdx\fuelly\Client

	public $id = 0;
	public $url = '';
	public $name = '';
	public $image = '';
	public $trend = array();

	/**
	 *
	 */
	public function __construct( Client $client, array $vehicle ) {
		$this->client = $client;

		$this->id = $vehicle['id'];
		$this->url = $vehicle['url'];
		$this->name = $vehicle['name'];
		$this->image = $vehicle['image'];

		if ( isset($vehicle['trend']) ) {
			$input = $client->createTrendInputConversion();

			foreach ( $vehicle['trend'] as $fuelup) {
				$this->trend[] = FuelUp::createFromTrend($this, $fuelup, $input);
			}

			// Sort by date DESC
			usort($this->trend, array(FuelUp::class, 'dateCmp'));
		}
	}

}
