<?php

namespace Sta\Entity\Validator;

use Sta\Util\StringFormats;
use Zend\Validator\AbstractValidator;

/**
 * @author: Stavarengo
 */
class CpfCnpj extends AbstractValidator
{

	const INVALIDO      = 'invalido';
	const CPF_INVALIDO  = 'cpfInvalido';
	const CNPJ_INVALIDO = 'cnpjInvalido';

	protected $messageTemplates = array(
		self::INVALIDO      => 'O número "%doc%" não é um documento válido.',
		self::CPF_INVALIDO  => 'O CPF "%doc%" não é válido.',
		self::CNPJ_INVALIDO => 'O CNPJ %doc% não é válido.',
	);
	protected $messageVariables = array(
		'doc' => 'doc',
	);

	/**
	 * @var string
	 */
	protected $doc;

	/**
	 * @param  mixed $value
	 *
	 * @return bool
	 */
	public function isValid($value)
	{
		if (\App\IsLocal::is()) {
			return true;
		}
		if (!is_string($value)) {
			$this->doc = $value;
			$this->error(self::INVALIDO);
			return false;
		} else if (strlen($value) <> 11 && strlen($value) <> 14) {
			$this->doc = $value;
			$this->error(self::INVALIDO);
			return false;
		} else {
			if (strlen($value) == 11) {
				if (!$this->validarCpf($value)) {
					$this->doc = StringFormats::formatarCpf($value);
					$this->error(self::CPF_INVALIDO);
					return false;
				}
			} else if (strlen($value) == 14) {
				if (!$this->validarCnpj($value)) {
					$this->doc = StringFormats::formatarCnpj($value);
					$this->error(self::CNPJ_INVALIDO);
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Calcula e retorna o digito verificador de um CPF.
	 *
	 * @param $cpfSemDv
	 *        Somente os 9 primeiros digitos do CPF.
	 *
	 * @return string
	 */
	public static function getCpfDv($cpfSemDv)
	{
		$soma = 0;

//		if (strlen($cpf) < 9) return '';

		// Verifica 1º dígito
		for ($i = 0; $i < 9; $i++) {
			$soma += (($i + 1) * $cpfSemDv[$i]);
		}

		$d1 = ($soma % 11);

		if ($d1 == 10) {
			$d1 = 0;
		}

		$soma = 0;

		// Verifica 2º dígito
		for ($i = 9, $j = 0; $i > 0; $i--, $j++) {
			$soma += ($i * $cpfSemDv[$j]);
		}

		$d2 = ($soma % 11);

		if ($d2 == 10) {
			$d2 = 0;
		}

		return $d1 . $d2;
	}

	/**
	 * @param string $cpf
	 *
	 * @return bool
	 */
	private function validarCpf($cpf)
	{
		$soma = 0;

		if (strlen($cpf) <> 11) return false;

		if (in_array($cpf, array('00000000000', '99999999999', '12345678900'))) return false;

		if (preg_match('/[ [:alpha:]]/', $cpf)) return false;

		$dv = self::getCpfDv(substr($cpf, 0, 9));

		if (!$dv || strlen($dv) != 2) {
			return false;
		}

		$d1 = $dv[0];
		$d2 = $dv[1];

		if ($d1 == $cpf[9] && $d2 == $cpf[10]) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param string $cnpj
	 *
	 * @return bool
	 */
	private function validarCnpj($cnpj)
	{

		if (strlen($cnpj) <> 14) return false;
		if (preg_match('/[ [:alpha:]]/', $cnpj)) return false;

		$dv = self::getCnpjDv(substr($cnpj, 0, 12));

		if (!$dv || strlen($dv) != 2) {
			return false;
		}

		$d1 = $dv[0];
		$d2 = $dv[1];

		if ($cnpj[12] == $d1 && $cnpj[13] == $d2) {
			return true;
		} else {
			return false;
		}
	}

	public static function getCnpjDv($cnpjSemDv)
	{

		if (strlen($cnpjSemDv) <> 12) return '';

		$soma = 0;

		$soma += ($cnpjSemDv[0] * 5);
		$soma += ($cnpjSemDv[1] * 4);
		$soma += ($cnpjSemDv[2] * 3);
		$soma += ($cnpjSemDv[3] * 2);
		$soma += ($cnpjSemDv[4] * 9);
		$soma += ($cnpjSemDv[5] * 8);
		$soma += ($cnpjSemDv[6] * 7);
		$soma += ($cnpjSemDv[7] * 6);
		$soma += ($cnpjSemDv[8] * 5);
		$soma += ($cnpjSemDv[9] * 4);
		$soma += ($cnpjSemDv[10] * 3);
		$soma += ($cnpjSemDv[11] * 2);

		$d1 = $soma % 11;
		$d1 = $d1 < 2 ? 0 : 11 - $d1;

		$soma = 0;
		$soma += ($cnpjSemDv[0] * 6);
		$soma += ($cnpjSemDv[1] * 5);
		$soma += ($cnpjSemDv[2] * 4);
		$soma += ($cnpjSemDv[3] * 3);
		$soma += ($cnpjSemDv[4] * 2);
		$soma += ($cnpjSemDv[5] * 9);
		$soma += ($cnpjSemDv[6] * 8);
		$soma += ($cnpjSemDv[7] * 7);
		$soma += ($cnpjSemDv[8] * 6);
		$soma += ($cnpjSemDv[9] * 5);
		$soma += ($cnpjSemDv[10] * 4);
		$soma += ($cnpjSemDv[11] * 3);
		$soma += ($d1 * 2);


		$d2 = $soma % 11;
		$d2 = $d2 < 2 ? 0 : 11 - $d2;

		return $d1 . $d2;
	}
}