<?php

namespace rdx\fuelly;

class UnitConversion {

	public $distance = '';
	public $volume = '';
	public $mileage = '';
	public $decimals = '';
	public $thousands = '';

	/**
	 * Dependency constructor
	 */
	public function __construct( $distance, $volume, $mileage, $decimals, $thousands ) {
		$this->distance = $distance;
		$this->volume = $volume;
		$this->mileage = $mileage;
		$this->decimals = $decimals;
		$this->thousands = $thousands;
	}

}
