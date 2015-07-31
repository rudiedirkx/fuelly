<?php

namespace rdx\fuelly;

use HTTP;
use InvalidArgumentException;

class Client {

	public $base = 'http://www.fuelly.com/';
	public $loginBase = 'https://m.fuelly.com/';

	public $mail = '';
	public $pass = '';
	public $dateFormat = 'd/m/Y';
	public $timeFormat = 'g:i a';

	public $session = '';
	public $username = '';

	public $log = array();

	/**
	 *
	 */
	public function getAllFuelups( $id ) {
		$response = $this->_get('car/make/model/2001/username/' . $id . '/export');
		if ( $token = $this->extractFormToken($response->body) ) {
			$response = $this->_post('exportfuelups', array(
				'data' => array(
					'_token' => $token,
					'usercar_id' => $id,
				),
			));
			if ( $response->code == 200 ) {
				$lines = array_filter(preg_split('#[\r\n]+#', $response->body));
				$header = $rows = array();
				foreach ($lines as $line) {
					$row = array_map('trim', str_getcsv($line));
					if ( !$header ) {
						$header = $row;
					}
					else {
						$rows[] = array_combine($header, $row);
					}
				}

				return $rows;
			}
		}
	}

	/**
	 *
	 */
	public function addFuelUp( $data ) {
		$data += array(
			'errorlevel' => 2,
			'price_per_unit' => '',
			'cost' => '',
			'city_pct' => '0',
			'fueltype_id' => '',
			'fuelup_date' => date($this->dateFormat),
			'time' => date($this->timeFormat),
			'paymenttype_id' => '',
			'fuelbrand' => '',
			'tirepsi' => '',
			'note' => '',
		);

		$required = array('usercar_id', 'miles_last_fuelup', 'amount');
		$missing = array_diff($required, array_keys(array_filter($data)));
		if ( $missing ) {
			throw new InvalidArgumentException('Missing params: ' . implode(', ', $missing));
		}

		// GET /fuelups/create
		$response = $this->_get('fuelups/create');

		if ( $token = $this->extractFormToken($response->body) ) {
			$data['_token'] = $token;

			// POST /fuelups/create
			$response = $this->_post('fuelups', array(
				'data' => $data,
			));
			if ( $response->code == 302 ) {
				$response = $this->_get($response->headers['location'][0]);

				// Take new fuelup ID from response and add it
				if ( $response->code == 200 ) {
					$regex = '#' . preg_quote($this->base, '#') . 'fuelups/(\d+)/edit#';
					if ( preg_match($regex, $response->body, $match) ) {
						$response->fuelup_id = $match[1];
					}
				}

				return $response;
			}
		}
	}

	/**
	 *
	 */
	public function getVehicles( $html ) {
		$response = $this->_get('dashboard');
		if ( $response->code == 200 ) {
			return $this->extractVehicles($response->body);
		}
	}

	/**
	 *
	 */
	protected function extractVehicles( $html ) {
		$regex = '#<ul class="dashboard-vehicle" data-clickable="([^"]+)">[\w\W]+?</ul>#';
		$vehicles = array();
		if ( preg_match_all($regex, $html, $matches) ) {
			foreach ( $matches[0] as $i => $html ) {
				$url = $matches[1][$i];

				preg_match('#/(\d+)$#', $url, $match);
				$id = $match[1];

				preg_match('#<h3[^>]*>(.+?)</h3>#', $html, $match);
				$name = htmlspecialchars_decode($match[1]);

				preg_match("#:\s*url\('/([^']+)'\)#", $html, $match);
				$image = $this->base . $match[1];

				preg_match("#data-trend='([^']+)'#", $html, $match);
				$trend = @json_decode($match[1], true) ?: false;

				$vehicles[] = compact('url', 'id', 'name', 'image', 'trend');
			}
		}

		return $vehicles;
	}

	/**
	 *
	 */
	public function logIn() {
		if ( !$this->mail || !$this->pass ) {
			return false;
		}

		// GET /login
		$response = $this->_get('login', array('login' => true));

		if ( $token = $this->extractFormToken($response->body) ) {
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

		return false;
	}

	/**
	 *
	 */
	public function checkSession() {
		if ( !$this->session ) {
			return false;
		}

		$response = $this->_get('dashboard');
		if ( $response->code == 200 ) {
			$regex = '#<a href="' . preg_quote($this->base, '#') . 'driver/([\w\d]+)/edit">Settings</a>#';
			if ( preg_match($regex, $response->body, $match) ) {
				$this->username = $match[1];

				// Since we're downloading /dashboard anyway, let's extract our vehicles from it
				$this->vehicles = $this->extractVehicles($response->body);

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
	 *
	 */
	protected function extractFormToken( $html ) {
		if ( preg_match('#<input.+?name="_token".+?>#i', $html, $match) ) {
			if ( preg_match('#value="([^"]+)"#', $match[0], $match) ) {
				return $match[1];
			}
		}
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
