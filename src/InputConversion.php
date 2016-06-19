<?php

namespace rdx\fuelly;

use rdx\fuelly\UnitConversion;
use rdx\units\Length;
use rdx\units\Mileage;
use rdx\units\Volume;

class InputConversion extends UnitConversion {

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
