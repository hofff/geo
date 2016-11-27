<?php

namespace Hofff\Geo;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class LatLngBounds {

	/**
	 * @var LatLng southwest corner
	 */
	private $sw;

	/**
	 * @var LatLng northeast corner
	 */
	private $ne;

	/**
	 * @param LatLng $sw southwest corner
	 * @param LatLng $ne northeast corner
	 */
	public function __construct(LatLng $sw, LatLng $ne) {
		$this->sw = $sw;
		$this->ne = $ne;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getSouthWest() . '|' . $this->getNorthEast();
	}

	/**
	 * @return LatLng
	 */
	public function getSouthWest() {
		return $this->sw;
	}

	/**
	 * @return LatLng
	 */
	public function getNorthEast() {
		return $this->ne;
	}

}
