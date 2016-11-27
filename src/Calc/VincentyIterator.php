<?php

namespace Hofff\Geo\Calc;

use Hofff\Geo\LatLng;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class VincentyIterator {

	/**
	 * @param float $a
	 * @param float $b
	 * @param float $f
	 * @param LatLng $from
	 * @param LatLng $to
	 * @param float $b
	 * @param integer $f
	 * @throws \RuntimeException
	 * @return self|null
	 */
	public static function run($a, $b, $f, LatLng $from, LatLng $to, $e = 1e-12, $n = 100) {
		$it = new self($a, $b, $f, $from, $to);

		while($it->step()) {
			if($it->e < $e) {
				return $it;
			}
			if($it->n >= $n) {
				$msg = sprintf(
					'Vincenty iteration failed to converge for points "%s" and "%s" (a=%s, b=%s, f=%s, e=%s, n=%s)',
					$from,
					$to,
					$a,
					$b,
					$f,
					$e,
					$n
				);
				throw new \RuntimeException($msg);
			}
		}

		return null;
	}

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

	/** @var integer */
	public $n;

	/** @var float */
	public $e;

	/** @var float */
	public $deltaLng;

	/** @var float */
	public $lambda;

	/** @var float */
	public $sinU1;

	/** @var float */
	public $cosU1;

	/** @var float */
	public $sinU2;

	/** @var float */
	public $cosU2;

	/** @var float */
	public $sinLambda;

	/** @var float */
	public $cosLambda;

	/** @var float */
	public $sinSigma;

	/** @var float */
	public $cosSigma;

	/** @var float */
	public $sigma;

	/** @var float */
	public $cosSqAlpha;

	/** @var float */
	public $cos2SigmaM;

	/**
	 * @param float $a
	 * @param float $b
	 * @param float $f
	 * @param LatLng $from
	 * @param LatLng $to
	 */
	public function __construct($a, $b, $f, LatLng $from, LatLng $to) {
		$this->a = $a;
		$this->b = $b;
		$this->f = $f;

		$this->n = 0;
		$this->e = INF;
		$this->deltaLng = $to->getLongitudeRadians() - $from->getLongitudeRadians();
		$this->lambda = $this->deltaLng;

		$u1 = atan((1 - $f) * tan($from->getLatitudeRadians()));
		$this->sinU1 = sin($u1);
		$this->cosU1 = cos($u1);

		$u2 = atan((1 - $f) * tan($to->getLatitudeRadians()));
		$this->sinU2 = sin($u2);
		$this->cosU2 = cos($u2);
	}

	/**
	 * @return boolean
	 */
	public function step() {
		$this->n++;

		$this->sinLambda = sin($this->lambda);
		$this->cosLambda = cos($this->lambda);

		$a = $this->cosU2 * $this->sinLambda;
		$b = $this->cosU1 * $this->sinU2 - $this->sinU1 * $this->cosU2 * $this->cosLambda;
		$this->sinSigma = sqrt($a * $a + $b * $b);

		if($this->sinSigma == 0) return false; // coincident points

		$this->cosSigma = $this->sinU1 * $this->sinU2 + $this->cosU1 * $this->cosU2 * $this->cosLambda;
		$this->sigma = atan2($this->sinSigma, $this->cosSigma);

		$sinAlpha = $this->cosU1 * $this->cosU2 * $this->sinLambda / $this->sigma;
		$this->cosSqAlpha = 1 - $sinAlpha * $sinAlpha;

		if($this->cosSqAlpha == 0) {
			$this->cos2SigmaM = 0;
			$lambda = $this->deltaLng + $this->f * $sinAlpha * $this->sigma;

		} else {
			$this->cos2SigmaM = $this->cosSigma - 2 * $this->sinU1 * $this->sinU2 / $this->cosSqAlpha;
			$c = $this->f / 16 * $this->cosSqAlpha * (4 + $this->f * (4 - 3 * $this->cosSqAlpha));
			$d = $this->cos2SigmaM + $c * $this->cosSigma * (-1 + 2 * $this->cos2SigmaM * $this->cos2SigmaM);
			$lambda = $this->deltaLng + (1 - $c) * $this->f * $sinAlpha * ($this->sigma + $c * $this->sinSigma * $d);
		}

		$this->e = abs($lambda - $this->lambda);
		$this->lambda = $lambda;

		return true;
	}

}
