<?php

namespace Hofff\Geo\Geocoding\Google;

use Hofff\Geo\LatLng;
use Hofff\Geo\LatLngBounds;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Result {

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @param array $data
	 */
	public function __construct(array $data) {
		$this->data = $data;
	}

	/**
	 * @return string|null
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function getFormattedAddress() {
		return $this->data['formatted_address'];
	}

	/**
	 * @return LatLng
	 */
	public function getLocation() {
		return new LatLng(
			$this->data['geometry']['location']['lat'],
			$this->data['geometry']['location']['lng']
		);
	}

	/**
	 * @return LatLngBounds|null
	 */
	public function getBounds() {
		if(!isset($this->data['geometry']['bounds'])) {
			return null;
		}

		$sw = new LatLng(
			$this->data['geometry']['bounds']['southwest']['lat'],
			$this->data['geometry']['bounds']['southwest']['lng']
		);
		$ne = new LatLng(
			$this->data['geometry']['bounds']['northeast']['lat'],
			$this->data['geometry']['bounds']['northeast']['lng']
		);
		return new LatLngBounds($sw, $ne);
	}

	/**
	 * @return LatLngBounds
	 */
	public function getViewport() {
		$sw = new LatLng(
			$this->data['geometry']['viewport']['southwest']['lat'],
			$this->data['geometry']['viewport']['southwest']['lng']
		);
		$ne = new LatLng(
			$this->data['geometry']['viewport']['northeast']['lat'],
			$this->data['geometry']['viewport']['northeast']['lng']
		);
		return new LatLngBounds($sw, $ne);
	}

	/**
	 * @return string|null
	 */
	public function getPlaceID() {
		return isset($this->data['place_id']) ? $this->data['place_id'] : null;
	}

	/**
	 * @return boolean
	 */
	public function isPartialMatch() {
		return isset($this->data['partial_match']) ? $this->data['partial_match'] : false;
	}

}
