<?php

namespace Hofff\Geo\Geocoding\Google;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Exception\TransferException;

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
	 * @var ClientInterface
	 */
	private $endpoint;

	/**
	 * @param string $key
	 * @param string $endpoint
	 * @param ClientInterface $client
	 */
	public function __construct($key, $endpoint = null, ClientInterface $client = null) {
		$this->key = $key === null ? null : (string) $key;
		$this->endpoint = $endpoint === null ? self::DEFAULT_ENDPOINT : (string) $endpoint;
		$this->client = $client ?: new Client;
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
	 * @return ClientInterface
	 */
	public function getClient() {
		return $this->client;
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
				http_build_query($this->compileQueryStringParams($query))
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
		$content = $this->execute($query);

		$results = [];
		if(isset($content['results'])) {
			foreach($content['results'] as $result) {
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
		$request = $this->createRequest($query);

		try {
			$response = $this->getClient()->send($request);
		} catch(TransferException $e) {
			throw new \RuntimeException('Geocoding request failed', 1, $e);
		}

		$content = json_decode($response->getBody()->getContents(), true);
		if(!is_array($content)) {
			throw new \RuntimeException('Unreadable geocoding response');
		}

		return $content;
	}

	/**
	 * @param Query $query
	 * @return RequestInterface
	 */
	public function createRequest(Query $query) {
		$params = $this->compileQueryStringParams($query);
		$uri = $this->getEndpoint() . '?' . http_build_query($params);
		return new Request('GET', $uri);;
	}

	/**
	 * @param Query $query
	 * @return array<string, string>
	 */
	public function compileQueryStringParams(Query $query) {
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
