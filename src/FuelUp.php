<?php

namespace rdx\fuelly;

use DateTime;
use rdx\fuelly\Vehicle;
use rdx\units\Length;
use rdx\units\Mileage;
use rdx\units\Volume;

class FuelUp {

	public $vehicle; // rdx\fuelly\Vehicle

	public $date; // DateTime
	public $volume; // rdx\units\Volume
	public $distance; // rdx\units\Length
	public $mileage; // rdx\units\Mileage

	public $raw_volume;
	public $raw_distance;

	/**
	 *
	 */
	public static function createFromTrend( Vehicle $vehicle, array $fuelup, InputConversion $input = null ) {
		// Trend is always in real numbers, and only its natives are reliable so use those
		$input or $input = $vehicle->client->createTrendInputConversion();

		// @todo Parse date correctly
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $fuelup['fuelup_date']);
		return new static($vehicle, $date, $fuelup['miles_last_fuelup'], $fuelup['amount'], $input);
	}

	/**
	 *
	 */
	public static function createFromDetail( Vehicle $vehicle, array $fuelup, InputConversion $input = null ) {
		// @todo Parse date correctly
		$date = DateTime::createFromFormat('d-m-y', $fuelup['fuelup_date']);
		return new static($vehicle, $date, $fuelup['miles_last_fuelup'], $fuelup['amount'], $input);
	}

	/**
	 *
	 */
	protected function __construct( Vehicle $vehicle, DateTime $date, $raw_distance, $raw_volume, InputConversion $input = null ) {
		$this->vehicle = $vehicle;

		$input or $input = $vehicle->client->input;

		$this->date = $date;

		$this->raw_volume = $input->convertNumber($raw_volume);
		$this->raw_distance = $input->convertNumber($raw_distance);

		$this->volume = $input->convertVolume($this->raw_volume);
		$this->distance = $input->convertDistance($this->raw_distance);

		$this->mileage = static::createMileage($this->distance, $this->volume);
	}

	/**
	 *
	 */
	public static function createMileage( Length $distance, Volume $volume ) {
		// Since we don't know the original mileage from here, we'll construct a known unit from known values
		return new Mileage($distance->to('km') / $volume->to('l'), 'kmpl');
	}

	/**
	 *
	 */
	public static function dateCmp( FuelUp $a, FuelUp $b ) {
		return $b->date->getTimestamp() - $a->date->getTimestamp();
	}

}
