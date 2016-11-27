<?php

namespace Hofff\Geo\Geocoding\Google;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Geocoder {

	/** @var string */
	const DEFAULT_ENDPOINT = 'https://maps.googleapis.com/maps/api/geocode/json';

	/** @var string */
	const STATUS_OK = 'OK';
	/** @var string */
	const STATUS_ZERO_RESULTS = 'ZERO_RESULTS';
	/** @var string */
	const STATUS_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';
	/** @var string */
	const STATUS_REQUEST_DENIED = 'REQUEST_DENIED';
	/** @var string */
	const STATUS_INVALID_REQUEST = 'INVALID_REQUEST';
	/** @var string */
	const STATUS_UNKNOWN_ERROR = 'UNKNOWN_ERROR';

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var string
	 */
	private $endpoint;

	/**
	 * @param string $key
	 */
	public function __construct($key, $endpoint = null) {
		$this->key = $key === null ? null : (string) $key;
		$this->endpoint = $endpoint === null ? self::DEFAULT_ENDPOINT : (string) $endpoint;
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getEndpoint() {
		return $this->endpoint;
	}

	/**
	 * @param Query $query
	 * @throws \RuntimeException
	 * @return LatLng
	 */
	public function locationFor(Query $query) {
		$results = $this->geocode($query);

		if(!$results) {
			throw new \RuntimeException(sprintf(
				'No results for query "%s"',
				$this->buildRequestURL($query)
			));
		}

		return $results[0]->getLocation();
	}

	/**
	 * @param Query $query
	 * @throws \RuntimeException
	 * @return array<Result>
	 */
	public function geocode(Query $query) {
		$response = $this->execute($query);

		if($response['status'] != self::STATUS_OK || $response['status'] != self::STATUS_ZERO_RESULTS) {
			$msg = sprintf(
				'Query to "%s" was unsuccessful with status "%s"',
				$this->buildRequestURL($query),
				$response['status']
			);

			isset($response['error_message']) && $msg .= ' (' . $response['error_message'] . ')';

			throw new \RuntimeException($msg);
		}

		$results = [];
		if(isset($response['results'])) {
			foreach($response['results'] as $result) {
				$results[] = new Result($result);
			}
		}

		return $results;
	}

	/**
	 * @param Query $query
	 * @throws \RuntimeException
	 * @return array
	 */
	public function execute(Query $query) {
		$params = $this->compileParams($query);
		$url = $this->getEndpoint() . '?' . http_build_query($params);

		$response = file_get_contents($url);
		if($response === false) {
			throw new \RuntimeException(sprintf('Request to "%s" failed', $url));
		}

		$response = json_decode($response, true);
		if(!is_array($response)) {
			throw new \RuntimeException(sprintf('Unreadable response for request to "%s"', $url));
		}

		return $response;
	}

	/**
	 * @param Query $query
	 * @return string
	 */
	public function buildRequestURL(Query $query) {
		$params = $this->compileParams($query);
		$url = $this->getEndpoint() . '?' . http_build_query($params);
		return $url;
	}

	/**
	 * @param Query $query
	 * @return array<string, string>
	 */
	public function compileParams(Query $query) {
		$params = [];

		if(null !== $key = $this->getKey()) {
			$params['key'] = $key;
		}

		if(null !== $address = $query->getAddress()) {
			$params['address'] = $address;
		}

		if(null !== $bounds = $query->getBounds()) {
			$params['bounds'] = $bounds->__toString();
		}

		if(null !== $language = $query->getLanguage()) {
			$params['language'] = $language;
		}

		if(null !== $region = $query->getRegion()) {
			$params['region'] = $region;
		}

		return $params;
	}

}
