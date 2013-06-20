<?php
namespace Sta\Util;

class EstadosBrasileiros
{
	protected static $estados = null;

	public static function isValidSigla($sigla)
	{
		$estados = self::getEstados();
		return isset($estados[$sigla]);
	}

	/**
	 * @param string $sigla
	 *
	 * @return EstadoBrasileiro
	 */
	public static function getEstado($sigla = null)
	{
		$estados = self::getEstados();
		return $estados[$sigla];
	}

	/**
	 * @return EstadoBrasileiro[]
	 */
	public static function getEstados()
	{
		if ( (!self::$estados)) {
			self::$estados = array(
				'AC' => new EstadoBrasileiro('AC', 'Acre'),
				'AL' => new EstadoBrasileiro('AL', 'Alagoas'),
				'AM' => new EstadoBrasileiro('AM', 'Amazonas'),
				'AP' => new EstadoBrasileiro('AP', 'Amapá'),
				'BA' => new EstadoBrasileiro('BA', 'Bahia'),
				'CE' => new EstadoBrasileiro('CE', 'Ceará'),
				'DF' => new EstadoBrasileiro('DF', 'Distrito Federal'),
				'ES' => new EstadoBrasileiro('ES', 'Espírito Santo'),
				'GO' => new EstadoBrasileiro('GO', 'Goiais'),
				'MA' => new EstadoBrasileiro('MA', 'Maranhão'),
				'MG' => new EstadoBrasileiro('MG', 'Minas Gerais'),
				'MS' => new EstadoBrasileiro('MS', 'Mato Grosso do Sul'),
				'MT' => new EstadoBrasileiro('MT', 'Mato Grosso'),
				'PA' => new EstadoBrasileiro('PA', 'Pará'),
				'PB' => new EstadoBrasileiro('PB', 'Paraíba'),
				'PE' => new EstadoBrasileiro('PE', 'Pernambuco'),
				'PI' => new EstadoBrasileiro('PI', 'Piauí'),
				'PR' => new EstadoBrasileiro('PR', 'Paraná'),
				'RJ' => new EstadoBrasileiro('RJ', 'Rio de Janeiro'),
				'RN' => new EstadoBrasileiro('RN', 'Rio Grande do Norte'),
				'RO' => new EstadoBrasileiro('RO', 'Rondônia'),
				'RR' => new EstadoBrasileiro('RR', 'Roraima'),
				'RS' => new EstadoBrasileiro('RS', 'Rio Grande do Sul'),
				'SC' => new EstadoBrasileiro('SC', 'Santa Catarina'),
				'SE' => new EstadoBrasileiro('SE', 'Sergipe'),
				'SP' => new EstadoBrasileiro('SP', 'São Paulo'),
				'TO' => new EstadoBrasileiro('TO', 'Tocantins'),
			);
		}

		return self::$estados;
	}
}