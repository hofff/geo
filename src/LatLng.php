<?php

namespace Hofff\Geo;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class LatLng {

	/**
	 * @param float $lat
	 * @param float $lng
	 * @throws \InvalidArgumentException
	 * @return self
	 */
	public static function createNormalized($lat, $lng) {
		if(!is_numeric($lat) || !is_numeric($lng)) {
			throw new \InvalidArgumentException('Arguments must be numeric');
		}
		$lat = self::normalizeLatitude($lat);
		$lng = self::normalizeLongitude($lng);
		return new self($lat, $lng);
	}

	/** @var float */
	private $lat;

	/** @var float */
	private $lng;

	/** @var float */
	private $latRad;

	/** @var float */
	private $lngRad;

	/**
	 * @param float $lat
	 * @param float $lng
	 * @throws \InvalidArgumentException
	 */
	public function __construct($lat, $lng) {
		if(!is_numeric($lat) || !is_numeric($lng)) {
			throw new \InvalidArgumentException('Arguments must be numeric');
		}

		$this->lat = (float) $lat;
		$this->lng = (float) $lng;
		$this->latRad = deg2rad($this->lat);
		$this->lngRad = deg2rad($this->lng);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->lat . ',' . $this->lng;
	}

	/**
	 * @return float
	 */
	public function getLatitude() {
		return $this->lat;
	}

	/**
	 * @return float
	 */
	public function getLatitudeRadians() {
		return $this->latRad;
	}

	/**
	 * @return float
	 */
	public function getLongitude() {
		return $this->lng;
	}

	/**
	 * @return float
	 */
	public function getLongitudeRadians() {
		return $this->lngRad;
	}

	/**
	 * @return self
	 */
	public function normalize() {
		return self::createNormalized($this->lat, $this->lng);
	}

	/**
	 * Normalize by clamping
	 *
	 * @param float $lat
	 * @return float
	 */
	public static function normalizeLatitude($lat) {
		return min(90, max(-90, $lat));
	}

	/**
	 * Normalize by folding
	 *
	 * @param float $lat
	 * @return float
	 */
	public static function normalizeLatitudeByFolding($lat) {
		$lat = fmod($lat, 360);

		if($lat > 270) {
			return -360 + $lat;
		}
		if($lat > 90) {
			return 180 - $lat;
		}
		if($lat < -270) {
			return 360 + $lat;
		}
		if($lat < -90) {
			return -180 - $lat;
		}

		return $lat;
	}

	/**
	 * @param float $lng
	 * @return float
	 */
	public static function normalizeLongitude($lng) {
		$lng = fmod($lng, 360);

		if(abs($lng) == 180) {
			return 180;
		}
		if($lng > 180) {
			return $lng - 360;
		}
		if($lng < -180) {
			return $lng + 360;
		}

		return $lng;
	}

}
