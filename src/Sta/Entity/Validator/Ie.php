<?php
namespace Sta\Entity\Validator;

use Sta\Entity\Validator\Exception\InvalidArgument;
use Zend\Validator\AbstractValidator;

/**
 * @author: Stavarengo
 */
class Ie extends AbstractValidator
{

	const IE_INVALIDA = 'ieInvalida';
	const UF_INVALIDA = 'ifInvalida';
	protected $messageTemplates = array(
		self::IE_INVALIDA => 'A inscrição estadual "%ie%" não é válida para a UF "%uf%".',
		self::UF_INVALIDA => 'A UF "%uf%" não é válida.',
	);
	protected $messageVariables = array(
		'ie' => 'ie',
		'uf' => 'uf',
	);
	/**
	 * @var string
	 */
	protected $ie;
	/**
	 * @var string
	 */
	protected $uf;

	/**
	 * @param IeValue $value
	 *
	 * @throws Exception\InvalidArgument
	 * @return bool
	 */
	public function isValid($value)
	{
		if (!$value instanceof IeValue) {
			throw new InvalidArgument();
		}

		$conf    = \Sta\Module::getServiceLocator()->get('Configuration');
		$staConf = $conf['sta'];
		if ($staConf['isLocal']()) {
			return true;
		}

		$ie = $value->ie;
		$uf = $value->uf;

		$uf     = strtoupper($uf);
		$method = "val$uf";
		if (is_callable(array($this, $method))) {
			if (!$this->$method($ie)) {
				$this->ie = $ie;
				$this->uf = $uf;
				$this->error(self::IE_INVALIDA);
				return false;
			}
		} else {
			$this->uf = $uf;
			$this->error(self::UF_INVALIDA);
			return false;
		}

		return true;
	}

	function valAC($ie)
	{
		if (strlen($ie) != 13) {
			return 0;
		} else {
			if (substr($ie, 0, 2) != '01') {
				return 0;
			} else {
				$b    = 4;
				$soma = 0;
				for ($i = 0; $i <= 10; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
					if ($b == 1) {
						$b = 9;
					}
				}
				$dig = 11 - ($soma % 11);
				if ($dig >= 10) {
					$dig = 0;
				}
				if (!($dig == $ie[11])) {
					return 0;
				} else {
					$b    = 5;
					$soma = 0;
					for ($i = 0; $i <= 11; $i++) {
						$soma += $ie[$i] * $b;
						$b--;
						if ($b == 1) {
							$b = 9;
						}
					}
					$dig = 11 - ($soma % 11);
					if ($dig >= 10) {
						$dig = 0;
					}

					return ($dig == $ie[12]);
				}
			}
		}
	}

	function valAL($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			if (substr($ie, 0, 2) != '24') {
				return 0;
			} else {
				$b    = 9;
				$soma = 0;
				for ($i = 0; $i <= 7; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
				}
				$soma *= 10;
				$dig = $soma - (((int)($soma / 11)) * 11);
				if ($dig == 10) {
					$dig = 0;
				}

				return ($dig == $ie[8]);
			}
		}
	}

	function valAM($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			$b    = 9;
			$soma = 0;
			for ($i = 0; $i <= 7; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			if ($soma <= 11) {
				$dig = 11 - $soma;
			} else {
				$r = $soma % 11;
				if ($r <= 1) {
					$dig = 0;
				} else {
					$dig = 11 - $r;
				}
			}

			return ($dig == $ie[8]);
		}
	}

	function valAP($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			if (substr($ie, 0, 2) != '03') {
				return 0;
			} else {
				$i = substr($ie, 0, -1);
				if (($i >= 3000001) && ($i <= 3017000)) {
					$p = 5;
					$d = 0;
				} elseif (($i >= 3017001) && ($i <= 3019022)) {
					$p = 9;
					$d = 1;
				} elseif ($i >= 3019023) {
					$p = 0;
					$d = 0;
				}

				$b    = 9;
				$soma = $p;
				for ($i = 0; $i <= 7; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
				}
				$dig = 11 - ($soma % 11);
				if ($dig == 10) {
					$dig = 0;
				} elseif ($dig == 11) {
					$dig = $d;
				}

				return ($dig == $ie[8]);
			}
		}
	}

	function valBA($ie)
	{
		if (strlen($ie) != 8) {
			return 0;
		} else {

			$arr1 = array('0', '1', '2', '3', '4', '5', '8');
			$arr2 = array('6', '7', '9');

			$i = substr($ie, 0, 1);

			if (in_array($i, $arr1)) {
				$modulo = 10;
			} elseif (in_array($i, $arr2)) {
				$modulo = 11;
			}

			$b    = 7;
			$soma = 0;
			for ($i = 0; $i <= 5; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}

			$i = $soma % $modulo;
			if ($modulo == 10) {
				if ($i == 0) {
					$dig = 0;
				} else {
					$dig = $modulo - $i;
				}
			} else {
				if ($i <= 1) {
					$dig = 0;
				} else {
					$dig = $modulo - $i;
				}
			}
			if (!($dig == $ie[7])) {
				return 0;
			} else {
				$b    = 8;
				$soma = 0;
				for ($i = 0; $i <= 5; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
				}
				$soma += $ie[7] * 2;
				$i = $soma % $modulo;
				if ($modulo == 10) {
					if ($i == 0) {
						$dig = 0;
					} else {
						$dig = $modulo - $i;
					}
				} else {
					if ($i <= 1) {
						$dig = 0;
					} else {
						$dig = $modulo - $i;
					}
				}

				return ($dig == $ie[6]);
			}
		}
	}

	function valCE($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			$b    = 9;
			$soma = 0;
			for ($i = 0; $i <= 7; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			$dig = 11 - ($soma % 11);

			if ($dig >= 10) {
				$dig = 0;
			}

			return ($dig == $ie[8]);
		}
	}

	function valDF($ie)
	{
		if (strlen($ie) != 13) {
			return 0;
		} else {
			if (substr($ie, 0, 2) != '07') {
				return 0;
			} else {
				$b    = 4;
				$soma = 0;
				for ($i = 0; $i <= 10; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
					if ($b == 1) {
						$b = 9;
					}
				}
				$dig = 11 - ($soma % 11);
				if ($dig >= 10) {
					$dig = 0;
				}

				if (!($dig == $ie[11])) {
					return 0;
				} else {
					$b    = 5;
					$soma = 0;
					for ($i = 0; $i <= 11; $i++) {
						$soma += $ie[$i] * $b;
						$b--;
						if ($b == 1) {
							$b = 9;
						}
					}
					$dig = 11 - ($soma % 11);
					if ($dig >= 10) {
						$dig = 0;
					}

					return ($dig == $ie[12]);
				}
			}
		}
	}

	function valES($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			$b    = 9;
			$soma = 0;
			for ($i = 0; $i <= 7; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			$i = $soma % 11;
			if ($i < 2) {
				$dig = 0;
			} else {
				$dig = 11 - $i;
			}

			return ($dig == $ie[8]);
		}
	}

	function valGO($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			$s = substr($ie, 0, 2);

			if (!(($s == 10) || ($s == 11) || ($s == 15))) {
				return 0;
			} else {
				$n = substr($ie, 0, 7);

				if ($n == 11094402) {
					if ($ie[8] != 0) {
						if ($ie[8] != 1) {
							return 0;
						} else {
							return 1;
						}
					} else {
						return 1;
					}
				} else {
					$b    = 9;
					$soma = 0;
					for ($i = 0; $i <= 7; $i++) {
						$soma += $ie[$i] * $b;
						$b--;
					}
					$i = $soma % 11;
					if ($i == 0) {
						$dig = 0;
					} else {
						if ($i == 1) {
							if (($n >= 10103105) && ($n <= 10119997)) {
								$dig = 1;
							} else {
								$dig = 0;
							}
						} else {
							$dig = 11 - $i;
						}
					}

					return ($dig == $ie[8]);
				}
			}
		}
	}

	function valMA($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			if (substr($ie, 0, 2) != 12) {
				return 0;
			} else {
				$b    = 9;
				$soma = 0;
				for ($i = 0; $i <= 7; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
				}
				$i = $soma % 11;
				if ($i <= 1) {
					$dig = 0;
				} else {
					$dig = 11 - $i;
				}

				return ($dig == $ie[8]);
			}
		}
	}

	function valMT($ie)
	{
		if (strlen($ie) != 11) {
			return 0;
		} else {
			$b    = 3;
			$soma = 0;
			for ($i = 0; $i <= 9; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
				if ($b == 1) {
					$b = 9;
				}
			}
			$i = $soma % 11;
			if ($i <= 1) {
				$dig = 0;
			} else {
				$dig = 11 - $i;
			}

			return ($dig == $ie[10]);
		}
	}

	function valMS($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			if (substr($ie, 0, 2) != 28) {
				return 0;
			} else {
				$b    = 9;
				$soma = 0;
				for ($i = 0; $i <= 7; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
				}
				$i = $soma % 11;
				if ($i == 0) {
					$dig = 0;
				} else {
					$dig = 11 - $i;
				}

				if ($dig > 9) {
					$dig = 0;
				}

				return ($dig == $ie[8]);
			}
		}
	}

	function valMG($ie)
	{
		if (strlen($ie) != 13) {
			return 0;
		} else {
			$ie2 = substr($ie, 0, 3) . '0' . substr($ie, 3);

			$b    = 1;
			$soma = "";
			for ($i = 0; $i <= 11; $i++) {
				$soma .= $ie2[$i] * $b;
				$b++;
				if ($b == 3) {
					$b = 1;
				}
			}
			$s = 0;
			for ($i = 0; $i < strlen($soma); $i++) {
				$s += $soma[$i];
			}
			$i   = substr($ie2, 9, 2);
			$dig = $i - $s;
			if ($dig != $ie[11]) {
				return 0;
			} else {
				$b    = 3;
				$soma = 0;
				for ($i = 0; $i <= 11; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
					if ($b == 1) {
						$b = 11;
					}
				}
				$i = $soma % 11;
				if ($i < 2) {
					$dig = 0;
				} else {
					$dig = 11 - $i;
				};

				return ($dig == $ie[12]);
			}
		}
	}

	function valPA($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			if (substr($ie, 0, 2) != 15) {
				return 0;
			} else {
				$b    = 9;
				$soma = 0;
				for ($i = 0; $i <= 7; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
				}
				$i = $soma % 11;
				if ($i <= 1) {
					$dig = 0;
				} else {
					$dig = 11 - $i;
				}

				return ($dig == $ie[8]);
			}
		}
	}

	function valPB($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			$b    = 9;
			$soma = 0;
			for ($i = 0; $i <= 7; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			$i = $soma % 11;
			if ($i <= 1) {
				$dig = 0;
			} else {
				$dig = 11 - $i;
			}

			if ($dig > 9) {
				$dig = 0;
			}

			return ($dig == $ie[8]);
		}
	}

	function valPR($ie)
	{
		if (strlen($ie) != 10) {
			return 0;
		} else {
			$b    = 3;
			$soma = 0;
			for ($i = 0; $i <= 7; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
				if ($b == 1) {
					$b = 7;
				}
			}
			$i = $soma % 11;
			if ($i <= 1) {
				$dig = 0;
			} else {
				$dig = 11 - $i;
			}

			if (!($dig == $ie[8])) {
				return 0;
			} else {
				$b    = 4;
				$soma = 0;
				for ($i = 0; $i <= 8; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
					if ($b == 1) {
						$b = 7;
					}
				}
				$i = $soma % 11;
				if ($i <= 1) {
					$dig = 0;
				} else {
					$dig = 11 - $i;
				}

				return ($dig == $ie[9]);
			}
		}
	}

	function valPE($ie)
	{
		if (strlen($ie) == 9) {
			$b    = 8;
			$soma = 0;
			for ($i = 0; $i <= 6; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			$i = $soma % 11;
			if ($i <= 1) {
				$dig = 0;
			} else {
				$dig = 11 - $i;
			}

			if (!($dig == $ie[7])) {
				return 0;
			} else {
				$b    = 9;
				$soma = 0;
				for ($i = 0; $i <= 7; $i++) {
					$soma += $ie[$i] * $b;
					$b--;
				}
				$i = $soma % 11;
				if ($i <= 1) {
					$dig = 0;
				} else {
					$dig = 11 - $i;
				}

				return ($dig == $ie[8]);
			}
		} elseif (strlen($ie) == 14) {
			$b    = 5;
			$soma = 0;
			for ($i = 0; $i <= 12; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
				if ($b == 0) {
					$b = 9;
				}
			}
			$dig = 11 - ($soma % 11);
			if ($dig > 9) {
				$dig = $dig - 10;
			}

			return ($dig == $ie[13]);
		} else {
			return 0;
		}
	}

	function valPI($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			$b    = 9;
			$soma = 0;
			for ($i = 0; $i <= 7; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			$i = $soma % 11;
			if ($i <= 1) {
				$dig = 0;
			} else {
				$dig = 11 - $i;
			}
			if ($dig >= 10) {
				$dig = 0;
			}

			return ($dig == $ie[8]);
		}
	}

	function valRJ($ie)
	{
		if (strlen($ie) != 8) {
			return 0;
		} else {
			$b    = 2;
			$soma = 0;
			for ($i = 0; $i <= 6; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
				if ($b == 1) {
					$b = 7;
				}
			}
			$i = $soma % 11;
			if ($i <= 1) {
				$dig = 0;
			} else {
				$dig = 11 - $i;
			}

			return ($dig == $ie[7]);
		}
	}

	function valRN($ie)
	{
		if (!((strlen($ie) == 9) || (strlen($ie) == 10))) {
			return 0;
		} else {
			$b = strlen($ie);
			if ($b == 9) {
				$s = 7;
			} else {
				$s = 8;
			}
			$soma = 0;
			for ($i = 0; $i <= $s; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			$soma *= 10;
			$dig = $soma % 11;
			if ($dig == 10) {
				$dig = 0;
			}

			$s += 1;
			return ($dig == $ie[$s]);
		}
	}

	function valRS($ie)
	{
		if (strlen($ie) != 10) {
			return 0;
		} else {
			$b    = 2;
			$soma = 0;
			for ($i = 0; $i <= 8; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
				if ($b == 1) {
					$b = 9;
				}
			}
			$dig = 11 - ($soma % 11);
			if ($dig >= 10) {
				$dig = 0;
			}

			return ($dig == $ie[9]);
		}
	}

	function valRO($ie)
	{
		if (strlen($ie) == 9) {
			$b    = 6;
			$soma = 0;
			for ($i = 3; $i <= 7; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			$dig = 11 - ($soma % 11);
			if ($dig >= 10) {
				$dig = $dig - 10;
			}

			return ($dig == $ie[8]);
		} elseif (strlen($ie) == 14) {
			$b    = 6;
			$soma = 0;
			for ($i = 0; $i <= 12; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
				if ($b == 1) {
					$b = 9;
				}
			}
			$dig = 11 - ($soma % 11);
			if ($dig > 9) {
				$dig = $dig - 10;
			}

			return ($dig == $ie[13]);
		} else {
			return 0;
		}
	}

	function valRR($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			if (substr($ie, 0, 2) != 24) {
				return 0;
			} else {
				$b    = 1;
				$soma = 0;
				for ($i = 0; $i <= 7; $i++) {
					$soma += $ie[$i] * $b;
					$b++;
				}
				$dig = $soma % 9;

				return ($dig == $ie[8]);
			}
		}
	}

	function valSC($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			$b    = 9;
			$soma = 0;
			for ($i = 0; $i <= 7; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			$dig = 11 - ($soma % 11);
			if ($dig <= 1) {
				$dig = 0;
			}

			return ($dig == $ie[8]);
		}
	}

	function valSP($ie)
	{
		if (strtoupper(substr($ie, 0, 1)) == 'P') {
			if (strlen($ie) != 13) {
				return 0;
			} else {
				$b    = 1;
				$soma = 0;
				for ($i = 1; $i <= 8; $i++) {
					$soma += $ie[$i] * $b;
					$b++;
					if ($b == 2) {
						$b = 3;
					}
					if ($b == 9) {
						$b = 10;
					}
				}
				$dig = $soma % 11;
				return ($dig == $ie[9]);
			}
		} else {
			if (strlen($ie) != 12) {
				return 0;
			} else {
				$b    = 1;
				$soma = 0;
				for ($i = 0; $i <= 7; $i++) {
					$soma += $ie[$i] * $b;
					$b++;
					if ($b == 2) {
						$b = 3;
					}
					if ($b == 9) {
						$b = 10;
					}
				}
				$dig = $soma % 11;
				if ($dig > 9) {
					$dig = 0;
				}

				if ($dig != $ie[8]) {
					return 0;
				} else {
					$b    = 3;
					$soma = 0;
					for ($i = 0; $i <= 10; $i++) {
						$soma += $ie[$i] * $b;
						$b--;
						if ($b == 1) {
							$b = 10;
						}
					}
					$dig = $soma % 11;

					return ($dig == $ie[11]);
				}
			}
		}
	}

	function valSE($ie)
	{
		if (strlen($ie) != 9) {
			return 0;
		} else {
			$b    = 9;
			$soma = 0;
			for ($i = 0; $i <= 7; $i++) {
				$soma += $ie[$i] * $b;
				$b--;
			}
			$dig = 11 - ($soma % 11);
			if ($dig > 9) {
				$dig = 0;
			}

			return ($dig == $ie[8]);
		}
	}

	function valTO($ie)
	{
		if (strlen($ie) != 11) {
			return 0;
		} else {
			$s = substr($ie, 2, 2);
			if (!(($s == '01') || ($s == '02') || ($s == '03') || ($s == '99'))) {
				return 0;
			} else {
				$b    = 9;
				$soma = 0;
				for ($i = 0; $i <= 9; $i++) {
					if (!(($i == 2) || ($i == 3))) {
						$soma += $ie[$i] * $b;
						$b--;
					}
				}
				$i = $soma % 11;
				if ($i < 2) {
					$dig = 0;
				} else {
					$dig = 11 - $i;
				}

				return ($dig == $ie[10]);
			}
		}
	}

}