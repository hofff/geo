<?php

namespace Hofff\Geo\Geocoding\Google;

use Hofff\Geo\LatLngBounds;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Query {

	/**
	 * @var string
	 */
	private $address;

	/**
	 * @var string|null
	 */
	private $language;

	/**
	 * @var LatLngBounds
	 */
	private $bounds;

	/**
	 * @var string|null
	 */
	private $region;

	/**
	 *
	 */
	public function __construct() {
	}

	/**
	 * @return string|null
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * @param string|null $address
	 * @return void
	 */
	public function setAddress($address) {
		$this->address = $address === null ? null : (string) $address;
	}

	/**
	 * @return LatLngBounds|null
	 */
	public function getBounds() {
		return $this->bounds;
	}

	/**
	 * @param LatLngBounds|null $bounds
	 * @return void
	 */
	public function setBounds(LatLngBounds $bounds = null) {
		$this->bounds = $bounds;
	}

	/**
	 * @return string|null
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * https://developers.google.com/maps/faq#languagesupport
	 *
	 * @param string|null $language
	 * @return void
	 */
	public function setLanguage($language) {
		$this->language = $language === null ? null : (string) $language;
	}

	/**
	 * @return string|null
	 */
	public function getRegion() {
		return $this->region;
	}

	/**
	 * @param string|null $region
	 * @return void
	 */
	public function setRegion($region) {
		$this->region = $region === null ? null : (string) $region;
	}

}
