<?php

namespace rdx\fuelly;

use rdx\units\Length;
use rdx\units\Mileage;
use rdx\units\Volume;

class InputConversion {

	public $distance = '';
	public $volume = '';
	public $mileage = '';
	public $thousands = '';
	public $decimals = '';

	/**
	 * Dependency constructor
	 */
	public function __construct( $distance, $volume, $mileage, $thousands, $decimals ) {
		$this->distance = $distance;
		$this->volume = $volume;
		$this->mileage = $mileage;
		$this->thousands = $thousands;
		$this->decimals = $decimals;
	}

	/**
	 *
	 */
	public function convertNumber( $number ) {
		// 1.000,45 => 1000,45
		$number = str_replace($this->thousands, '', $number);

		// 1000,45 => 1000.45
		if ( $this->decimals != '.' ) {
			$number = str_replace($this->decimals, '.', $number);
		}

		return (float) $number;
	}

	/**
	 *
	 */
	public function convertDistance( $distance ) {
		return new Length($distance, $this->distance);
	}

	/**
	 *
	 */
	public function convertVolume( $volume ) {
		return new Volume($volume, $this->volume);
	}

	/**
	 *
	 */
	public function convertMileage( $mileage ) {
		return new Mileage($mileage, $this->mileage);
	}

}
