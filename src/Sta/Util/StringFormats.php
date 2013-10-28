<?php
namespace Sta\Util;

class StringFormats
{

	/**
	 * Formata um número de telefone.
	 * Créditos:
	 *    Função original - http://www.danielkassner.com/2010/05/21/format-us-phone-number-using-php
	 *    Adaptada por Rafael Stavarengo
	 *
	 * @param string $phone
	 *        O telefone que será formatado.
	 * @param boolean $convert
	 *        Default true. Determina se as letras serão corespondidas em seus respectivos números.
	 *        Exemplo:
	 *            1-800-TERMINIX, vira (180) 0837-6464
	 *            1-800-Flowers, vira (180) 0356-9377
	 *            18-3666-Sony, vira (18) 3666-7669
	 *
	 * @return string
	 */
	public static function formatPhone($phone, $convert = true)
	{
		// If we have not entered a phone number just return empty
		if (empty($phone)) {
			return '';
		}

		// Strip out any extra characters that we do not need only keep letters and numbers
		$phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);
		// Keep original phone in case of problems later on but without special characters
		$OriginalPhone = $phone;

		// Do we want to convert phone numbers with letters to their number equivalent?
		// Samples are: 1-800-TERMINIX, 1-800-FLOWERS, 1-800-Petmeds
		if ($convert == true && !is_numeric($phone)) {
			$replace = array(
				'2' => array('a', 'b', 'c'),
				'3' => array('d', 'e', 'f'),
				'4' => array('g', 'h', 'i'),
				'5' => array('j', 'k', 'l'),
				'6' => array('m', 'n', 'o'),
				'7' => array('p', 'q', 'r', 's'),
				'8' => array('t', 'u', 'v'),
				'9' => array('w', 'x', 'y', 'z')
			);

			// Replace each letter with a number
			// Notice this is case insensitive with the str_ireplace instead of str_replace
			foreach ($replace as $digit => $letters) {
				$phone = str_ireplace($letters, $digit, $phone);
			}
		}

		$length = strlen($phone);
		// Perform phone number formatting here
		switch ($length) {
			case 7:
			case 8:
				// Format: xxx-xxxx ou xxxx-xxxx
				return preg_replace("/([0-9a-zA-Z]{3,4})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
			case 10:
				// Format: (xx) xxxx-xxxx
				return preg_replace("/([0-9a-zA-Z]{2})([0-9a-zA-Z]{4})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);
			case 11:
				// Format: (xxx) xxxx-xxxx
				return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);
			default:
				// Return original phone if not 7, 10 or 11 digits long
				return $OriginalPhone;
		}
	}

	public static function formatarCpf($cpf)
	{
		$cpf = trim($cpf);
		if (!$cpf) return $cpf;

		// Remove todos os carecteres, exceto os números
		$cpf = preg_replace("/[^0-9]/", "", $cpf);

		$length = strlen($cpf);
		if ($length == 11) {
			return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})/", "$1.$2.$3-$4", $cpf);
		} else {
			return $cpf;
		}
	}

	public static function formatarCnpj($cnpj)
	{
		$cnpj = trim($cnpj);
		if (!$cnpj) return $cnpj;

		// Remove todos os carecteres, exceto os números
		$cnpj = preg_replace("/[^0-9]/", "", $cnpj);

		$length = strlen($cnpj);
		if ($length == 14) {
			return preg_replace("/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{2})/", "$1.$2.$3/$4-$5", $cnpj);
		} else {
			return $cnpj;
		}
	}

	public static function formatarCep($cep)
	{
		$cep = trim($cep);
		if (!$cep) return $cep;

		// Remove todos os carecteres, exceto os números
		$cep = preg_replace("/[^0-9]/", "", $cep);

		$length = strlen($cep);
		if ($length == 8) {
			return preg_replace("/([0-9]{5})([0-9]{3})/", "$1-$2", $cep);
		} else {
			return $cep;
		}
	}

	public static function formatarCpfCnpj($cpfCnpj)
	{
		$length = strlen($cpfCnpj);
		if ($length == 14) {
			return self::formatarCnpj($cpfCnpj);
		}
		return self::formatarCpf($cpfCnpj);
	}

	public static function printSQL($sql, $return = false)
	{
		if ($sql instanceof \Doctrine\ORM\QueryBuilder) {
			$sql = $sql->getQuery()->getSQL();
		}
		$sql = str_replace('SELECT ', "SELECT\n\t", $sql);
		$sql = str_replace(', ', "\n\t", $sql);
		$sql = str_replace('`, ', "`,\n\t", $sql);
		$sql = str_replace(' FROM ', "\nFROM ", $sql);
		$sql = str_replace(' INNER JOIN ', "\nINNER JOIN ", $sql);
		$sql = str_replace(' LEFT JOIN ', "\nLEFT JOIN ", $sql);
		$sql = str_replace(') AND (', ")\nAND (", $sql);
		$sql = str_replace(' WHERE ', "\nWHERE ", $sql);
		$sql = str_replace(' GROUP BY', "\nGROUP BY", $sql);
		$sql = str_replace(' ORDER BY', "\nORDER BY", $sql);
		$sql = str_replace(' WHERE ', "\nWHERE ", $sql);
		$sql = str_replace(' LIMIT ', "\nLIMIT ", $sql);

		if ($return) {
			return $sql;
		} else {
			echo $sql;
		}
	}

}