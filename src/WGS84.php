<?php

namespace Hofff\Geo;

use Hofff\Geo\Calc\Haversine;
use Hofff\Geo\Calc\Rhumb;
use Hofff\Geo\Calc\Vincenty;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class WGS84 {

	/**
	 * @var float
	 */
	const EARTH_RADIUS = 6371000.0;

	/**
	 * @var float Semi-major axis
	 */
	const A = 6378137.0;

	/**
	 * @var float Semi-minor axis
	 */
	const B = 6356752.314245;

	/**
	 * @var float Inverse flattening
	 */
	const FR = 298.257223563;

	/**
	 * @return Haversine
	 */
	public static function createHaversine() {
		return new Haversine(self::EARTH_RADIUS);
	}

	/**
	 * @return Rhumb
	 */
	public static function createRhumb() {
		return new Rhumb(self::EARTH_RADIUS);
	}

	/**
	 * @return Vincenty
	 */
	public static function createVincenty() {
		return new Vincenty(self::A, self::B, 1 / self::FR);
	}

}
