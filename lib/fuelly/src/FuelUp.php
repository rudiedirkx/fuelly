<?php

namespace rdx\fuelly;

use rdx\fuelly\Vehicle;
use DateTime;
use rdx\units\Mileage;

class FuelUp {

	public $vehicle; // rdx\fuelly\Vehicle

	public $date; // DateTime

	/**
	 *
	 */
	public function __construct( Vehicle $vehicle, array $fuelup ) {
		// $this->vehicle = $vehicle;

		// @todo Parse date correctly
		// @todo Parse amount correctly
		// @todo Parse distance correctly

		$this->date =
			DateTime::createFromFormat('Y-m-d H:i:s', $fuelup['fuelup_date']) ?:
			DateTime::createFromFormat('d-m-y', $fuelup['fuelup_date']);

		$this->amount = self::number(@$fuelup['raw_amount'] ?: $fuelup['amount']);
		$this->distance = self::number($fuelup['miles_last_fuelup']);

		$this->mileage = new Mileage($this->distance / $this->amount);

		// $this->original = $fuelup;
	}

	/**
	 *
	 */
	public static function number( $str ) {
		return (float) str_replace(',', '.', $str);
	}

	/**
	 *
	 */
	public static function dateCmp( FuelUp $a, FuelUp $b ) {
		return $b->date->getTimestamp() - $a->date->getTimestamp();
	}

}
