<?php

class Fuelly {

	public $base = 'http://www.fuelly.com/';
	public $loginBase = 'https://m.fuelly.com/';

	public $mail = '';
	public $pass = '';

	public $session = '';
	public $username = '';

	public $log = array();

	/**
	 *
	 */
	public function logIn() {
		if ( !$this->mail || !$this->pass ) {
			return false;
		}

		// GET /login
		$response = $this->_get('login', array('login' => true));

		$form = $response->body;
		if ( preg_match('#<input.+?name="_token".+?>#i', $form, $match) ) {
			if ( preg_match('#value="([^"]+)"#', $match[0], $match) ) {
				$token = $match[1];

				// POST /login
				$response = $this->_post('login', array(
					'login' => true,
					'cookies' => $response->cookies,
					'data' => array(
						'_token' => $token,
						'email' => $this->mail,
						'password' => $this->pass,
					),
				));
				$this->session = $response->cookies_by_name['fuelly_session'][0];
				return $this->checkSession();
			}
		}

		return false;
	}

	/**
	 *
	 */
	public function checkSession() {
		$response = $this->_get('dashboard');
		if ( $response->code == 200 ) {
			$regex = '#<a href="' . preg_quote($this->base, '#') . 'driver/([\w\d]+)/edit">Settings</a>#';
			if ( preg_match($regex, $response->body, $match) ) {
				$this->username = $match[1];
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 */
	public function refreshSession() {
		if ( !$this->checkSession() ) {
			return $this->logIn();
		}

		return true;
	}



	/**
	 * HTTP GET
	 */
	public function _get( $uri, $options = array() ) {
		return $this->_http($uri, $options + array('method' => 'GET'));
	}

	/**
	 * HTTP POST
	 */
	public function _post( $uri, $options = array() ) {
		return $this->_http($uri, $options + array('method' => 'POST'));
	}

	/**
	 * HTTP URL
	 */
	public function _url( $uri, $options = array() ) {
		$base = !empty($options['login']) ? $this->loginBase : $this->base;
		$url = strpos($uri, '://') ? $uri : $base . $uri;
		return $url;
	}

	/**
	 * HTTP REQUEST
	 */
	public function _http( $uri, $options = array() ) {
		if ($this->session) {
			$options['cookies'][] = array('fuelly_session', $this->session);
		}

		$url = $this->_url($uri, $options);
		$log['req'] = $options['method'] . ' ' . $url;
		$request = HTTP::create($url, $options);

		$response = $request->request();
		$log['rsp'] = $response->code . ' ' . $response->status;

		$this->log[] = $log;

		return $response;
	}

}
