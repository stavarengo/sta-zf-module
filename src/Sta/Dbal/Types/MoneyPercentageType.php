<?php

namespace Sta\Dbal\Types;

/**
 * Tipo para colunas que armazenam valores monetario ou percentuais.
 * Retorna a mesma declaração do percentual, porque o percentual é maior que o monetario.
 */
class MoneyPercentageType extends PercentageType
{

	const MONEY_PERCENTAGE = 'moneyPercentage';

	public function getName()
	{
		return self::MONEY_PERCENTAGE;
	}
}
