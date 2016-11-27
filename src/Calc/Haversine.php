<?php

namespace Hofff\Geo\Calc;

use Hofff\Geo\LatLng;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Haversine {

	/**
	 * @var float
	 */
	private $earthRadius;

	/**
	 * @param float $earthRadius
	 */
	public function __construct($earthRadius) {
		$this->earthRadius = $earthRadius;
	}

	/**
	 * @param LatLng $from
	 * @param LatLng $to
	 * @return float
	 */
	public function distance(LatLng $from, LatLng $to) {
		$fromLat = $from->getLatitudeRadians();
		$toLat = $to->getLatitudeRadians();

		$deltaLat = $toLat - $fromLat;
		$deltaLng = $to->getLongitudeRadians() - $from->getLongitudeRadians();

		$a = sin($deltaLat / 2);
		$b = sin($deltaLng / 2);
		$c = $a * $a + $b * $b * cos($fromLat) * cos($toLat);
		$d = 2 * atan2(sqrt($c), sqrt(1 - $c));

		return $d * $this->earthRadius;
	}

	/**
	 * @param LatLng $from
	 * @param LatLng $to
	 * @param float $x
	 * @return float
	 */
	public function bearing(LatLng $from, LatLng $to, $x = 0.0) {
		if($x == 1) {
			return fmod($this->bearing($to, $from) + 180, 360);
		}

		if($x != 0) {
			throw new \UnexpectedValueException('Intermediate bearing not implemented');
		}

		$fromLat = $from->getLatitudeRadians();
		$toLat = $to->getLatitudeRadians();

		$deltaLng = $to->getLongitudeRadians() - $from->getLongitudeRadians();

		$a = sin($deltaLng) * cos($toLat);
		$b = cos($fromLat) * sin($toLat) - sin($fromLat) * cos($toLat) * cos($deltaLng);
		$bearing = atan2($a, $b);

		return fmod(rad2deg($bearing) + 360, 360);
	}

	/**
	 * @param LatLng $from
	 * @param float $bearing
	 * @param float $distance
	 * @return LatLng
	 */
	public function destination(LatLng $from, $bearing, $distance) {
		$pi = pi();
		$fromLat = $from->getLatitudeRadians();
		$distance /= $this->earthRadius;
		$bearing = deg2rad($bearing);

		$toLat = asin(sin($fromLat) * cos($distance) + cos($fromLat) * sin($distance) * cos($bearing));

		$a = sin($bearing) * sin($distance) * cos($fromLat);
		$b = cos($distance) - sin($fromLat) * sin($toLat);
		$toLng = $from->getLongitudeRadians() + atan2($a, $b);
		$toLng = fmod($toLng + 3 * $pi, 2 * $pi) - $pi;

		return new LatLng(rad2deg($toLat), rad2deg($toLng));
	}

}
