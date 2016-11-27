<?php

namespace Hofff\Geo\Calc;

use Hofff\Geo\LatLng;
use Hofff\Geo\LatLngBounds;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Rhumb {

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
		$pi = pi();
		$fromLat = $from->getLatitudeRadians();
		$toLat = $to->getLatitudeRadians();

		$deltaLat = $toLat - $fromLat;

		$deltaLng = abs($to->getLongitudeRadians() - $from->getLongitudeRadians());
		$deltaLng > $pi && $deltaLng = 2 * $pi - $deltaLng;

		$deltaPhi = $this->calculateDeltaPhi($fromLat, $toLat);

		// E-W line gives $deltaPhi = 0
		$q = $deltaPhi != 0 ? $deltaLat / $deltaPhi : cos($fromLat);

		return sqrt($deltaLat * $deltaLat + $q * $q * $deltaLng * $deltaLng) * $this->earthRadius;
	}

	/**
	 * @param LatLng $from
	 * @param LatLng $to
	 * @param float $x
	 * @return float
	 */
	public function bearing(LatLng $from, LatLng $to, $x = 0.0) {
		$deltaLng = $to->getLongitudeRadians() - $from->getLongitudeRadians();
		abs($deltaLng) > pi() && $deltaLng = ($deltaLng > 0 ? -1 : 1) * (2 * pi() - $deltaLng);

		$deltaPhi = $this->calculateDeltaPhi($from->getLatitudeRadians(), $to->getLatitudeRadians());

		$bearing = atan2($deltaLng, $deltaPhi);

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

		$toLat = $fromLat + $distance * cos($bearing);
		$deltaLat = $toLat - $fromLat;

		$deltaPhi = $this->calculateDeltaPhi($fromLat, $toLat);
		$q = $deltaPhi != 0 ? $deltaLat / $deltaPhi : cos($fromLat);

		$deltaLng = $distance * sin($bearing) / $q;

		abs($toLat) > $pi / 2 && $toLat = ($toLat > 0 ? -1 : 1) * ($pi - $toLat);

		$toLng = fmod($from->getLongitudeRadians() + $deltaLng + 3 * $pi, 2 * $pi) - $pi;

		return new LatLng(rad2deg($toLat), rad2deg($toLng));
	}

	/**
	 * @param LatLngBounds $bounds
	 * @return LatLng
	 */
	public function center(LatLngBounds $bounds) {
		$sw = $bounds->getSouthWest();
		$ne = $bounds->getNorthEast();
		$bearing = $this->bearing($sw, $ne);
		$distance = $this->distance($sw, $ne);
		return $this->destination($sw, $bearing, $distance / 2);
	}

	/**
	 * @param LatLng $center
	 * @param float $radius
	 * @return LatLngBounds
	 */
	public function boundsOfCircle(LatLng $center, $radius) {
		if($radius <= 0) {
			return new LatLngBounds($center, $center);
		}

		$radius = sqrt(2 * $radius * $radius);
		$sw = $this->destination($center, 225, $radius);
		$ne = $this->destination($center, 45, $radius);

		return new LatLngBounds($sw, $ne);
	}

	/**
	 * @param float $fromLat
	 * @param float $toLat
	 * @return float
	 */
	public function calculateDeltaPhi($fromLat, $toLat) {
		return log(tan($toLat / 2 + pi() / 4) / tan($fromLat / 2 + pi() / 4));
	}

}
