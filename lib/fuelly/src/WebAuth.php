<?php

namespace rdx\fuelly;

class WebAuth {

	public $mail = '';
	public $pass = '';
	public $session = '';

	/**
	 * Dependency constructor
	 */
	public function __construct( $mail, $pass, $session = '' ) {
		$this->mail = $mail;
		$this->pass = $pass;
		$this->session = $session;
	}

}
