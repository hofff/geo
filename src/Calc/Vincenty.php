<?php

namespace Hofff\Geo\Calc;

use Hofff\Geo\LatLng;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Vincenty {

	/**
	 * @var float Semi-major axis
	 */
	private $a;

	/**
	 * @var float Semi-minor axis
	 */
	private $b;

	/**
	 * @var float Flattening
	 */
	private $f;

	/**
	 * @param float $a
	 * @param float $b
	 * @param float $f
	 */
	public function __construct($a, $b, $f) {
		$this->a = $a;
		$this->b = $b;
		$this->f = $f;
	}

	/**
	 * @param LatLng $from
	 * @param LatLng $to
	 * @return float
	 */
	public function distance(LatLng $from, LatLng $to) {
		if(!$it = VincentyIterator::run($this->a, $this->b, $this->f, $from, $to)) {
			return 0;
		}

		$uSq = $it->cosSqAlpha * ($this->a * $this->a - $this->b * $this->b) / ($this->b * $this->b);
		$a = 1 + $uSq / 16384 * (4096 + $uSq * (-768 + $uSq * (320 - 175 * $uSq)));
		$b = $uSq / 1024 * (256 + $uSq * (-128 + $uSq * (74 - 47 * $uSq)));
		$c = $it->cosSigma * (-1 + 2 * $it->cos2SigmaM * $it->cos2SigmaM);
		$d = $b / 6 * $it->cos2SigmaM * (-3 + 4 * $it->sinSigma * $it->sinSigma) * (-3 + 4 * $it->cos2SigmaM * $it->cos2SigmaM);
		$deltaSigma = $b * $it->sinSigma * ($it->cos2SigmaM + $b / 4 * ($c - $d));

		$distance = $this->b * $a * ($it->sigma - $deltaSigma);

		return $distance;
	}

	/**
	 * @param LatLng $from
	 * @param LatLng $to
	 * @param float $x
	 * @return float
	 */
	public function bearing(LatLng $from, LatLng $to, $x = 0.0) {
		if($x != 0 && $x != 1) {
			throw new \UnexpectedValueException('Intermediate bearing not implemented');
		}

		if(!$it = VincentyIterator::run($this->a, $this->b, $this->f, $from, $to)) {
			return 0;
		}

		if($x == 0) {
			$a = $it->cosU2 * $it->sinLambda;
			$b = $it->cosU1 * $it->sinU2 - $it->sinU1 * $it->cosU2 * $it->cosLambda;
		} else {
			$a = $it->cosU1 * $it->sinLambda;
			$b = -1 * $it->sinU1 * $it->cosU2 + $it->cosU1 * $it->sinU2 * $it->cosLambda;
		}

		return rad2deg(atan2($a, $b));
	}

}
