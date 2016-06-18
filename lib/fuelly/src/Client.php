<?php

namespace rdx\fuelly;

use InvalidArgumentException;
use rdx\fuelly\FuelUp;
use rdx\fuelly\UnitConversion;
use rdx\fuelly\Vehicle;
use rdx\fuelly\WebAuth;
use rdx\http\HTTP;

class Client {

	public $base = 'http://www.fuelly.com/';
	public $loginBase = 'https://m.fuelly.com/';

	public $dateFormat = 'd/m/Y';
	public $timeFormat = 'g:i a';

	public $auth; // rdx\fuelly\WebAuth
	public $input; // rdx\fuelly\InputConversion
	public $username = '';
	public $vehicles = array();

	public $log = array();

	/**
	 * Dependency constructor
	 */
	public function __construct( WebAuth $auth, InputConversion $input ) {
		$this->auth = $auth;
		$this->input = $input;
	}

	/**
	 *
	 */
	public function createTrendInputConversion() {
		// Trend is always in real numbers, and only its natives are reliable so use those
		return new InputConversion('ml', 'usg', $this->input->mileage, '.', ',');
	}

	/**
	 *
	 */
	public function getFuelUp( $id ) {
		$fuelup = compact('id');

		$response = $this->_get('fuelups/' . $id . '/edit');

		preg_match_all('#<input[\s\S]+?name="([^"]+)"[\s\S]+?>#', $response->body, $matches, PREG_SET_ORDER);
		foreach ( $matches as $match ) {
			if ( in_array($match[1], array('_token', 'miles_last_fuelup', 'price_per_unit', 'amount', 'fuelup_date')) ) {
				preg_match('#value="([^"]+)"#', $match[0], $match2);
				$fuelup[ $match[1] ] = $match2[1];
			}
		}

		preg_match('#<textarea[^>]+name="note"[^>]*>([^>]+)</textarea>#', $response->body, $match);
		$fuelup['note'] = trim(@$match[1]);

		return $fuelup;
	}

	/**
	 *
	 */
	public function updateFuelUp( $id, $data ) {
		$data = $this->_validateFuelUpData($data);
		unset($data['id']);

		if ( !isset($data['_token']) ) {
			$response = $this->_get('fuelups/' . $id . '/edit');
			$data['_token'] = $this->extractFormToken($response->body);
		}

		$data['_method'] = 'PUT';
		$response = $this->_post('fuelups/' . $id, array(
			'data' => $data,
		));
		return $response->code == 302;
	}

	/**
	 *
	 */
	public function _validateFuelUpData( $data ) {
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

		return $data;
	}

	/**
	 *
	 */
	public function getFuelUpsWithIds( Vehicle $vehicle, $limit = 15 ) {
		$query = http_build_query(array(
			'iDisplayStart' => 0,
			'iDisplayLength' => $limit,
			'sSortDir_0' => 'desc',
			'usercar_id' => $vehicle->id,
		));
		$response = $this->_get('ajax/fuelup-log?' . $query);
		if ( $response->code == 200 ) {
			if ( $response->response ) {
				$fuelups = array();
				foreach ( $response->response['aaData'] as $fuelup ) {
					if ( preg_match('#fuelups/(\d+)/edit#', $fuelup[0], $match) ) {
						$fuelup = array(
							'id' => $match[1],
							'usercar_id' => $vehicle->id,
							'fuelup_date' => $fuelup[2][0],
							'miles_last_fuelup' => $fuelup[3][0],
							'amount' => $fuelup[4][0],
						);

						$fuelups[ $fuelup['id'] ] = FuelUp::createFromDetail($vehicle, $fuelup);
					}
				}

				// Sort by date DESC
				uasort($fuelups, array(FuelUp::class, 'dateCmp'));

				return $fuelups;
			}
		}

		return array();
	}

	/**
	 *
	 */
	public function getAllFuelups( Vehicle $vehicle ) {
		$response = $this->_get('car/make/model/2001/username/' . $vehicle->id . '/export');
		if ( $token = $this->extractFormToken($response->body) ) {
			$response = $this->_post('exportfuelups', array(
				'data' => array(
					'_token' => $token,
					'usercar_id' => $vehicle->id,
				),
			));
			if ( $response->code == 200 ) {
				$lines = array_filter(preg_split('#[\r\n]+#', $response->body));
				$header = $rows = array();
				foreach ( $lines as $line ) {
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
		$data = $this->_validateFuelUpData($data);

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
	public function getVehicle( $id ) {
		$vehicles = $this->getVehicles();
		return @$vehicles[$id];
	}

	/**
	 *
	 */
	public function getVehicles() {
		// Must exist, because session must be valid, so we did a `GET /dashboard`
		return $this->vehicles;
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

				$vehicles[$id] = new Vehicle($this, compact('url', 'id', 'name', 'image', 'trend'));
			}
		}

		return $vehicles;
	}

	/**
	 *
	 */
	public function logIn() {
		if ( !$this->auth->mail || !$this->auth->pass ) {
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
					'email' => $this->auth->mail,
					'password' => $this->auth->pass,
				),
			));
			$this->auth->session = $response->cookies_by_name['fuelly_session'][0];
			return $this->checkSession();
		}

		return false;
	}

	/**
	 *
	 */
	public function checkSession() {
		if ( !$this->auth->session ) {
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
	public function ensureSession() {
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
		if ($this->auth->session) {
			$options['cookies'][] = array('fuelly_session', $this->auth->session);
		}

		$url = $this->_url($uri, $options);

		$log = array();
		$log['req'] = $options['method'] . ' ' . $url;
		$this->log[] = &$log;

		$_start = microtime(1);
		$request = HTTP::create($url, $options);

		$response = $request->request();
		$_time = microtime(1) - $_start;

		$log['rsp'] = $response->code . ' ' . $response->status;
		$log['time'] = $_time;

		return $response;
	}

}
