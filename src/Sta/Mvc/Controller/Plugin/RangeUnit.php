<?php
namespace Sta\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * @author Stavarengo
 */
class RangeUnit extends AbstractPlugin
{

	/**
	 *
	 * @param int $maxLength
	 *        Quantidade máxima permitida para o range. Tamanho total da entidade.
	 *
	 * @param string $unit
	 *        Unidade de medida da entidade. Ex: items, bytes.
	 *
	 * @param \Zend\Http\Header\HeaderInterface $rawHeader
	 *        Default null. A entidade Range do cabeçalho da requisição.
	 *        Quando null será usado o valor retornado pelo método {@link getRawHeader() }.
	 *
	 * @param bool $acceptQueryParams
	 *        Default true. Quando true, se o cabeçalho Range não existir, nós tentaremos montar um range usando os
	 *        parâmetros GET 'start' e 'count'.
	 *
	 * @return \Sta\Util\RangeUnit
	 */
	public function __invoke($maxLength, $unit = 'items', \Zend\Http\Header\HeaderInterface $rawHeader = null,
		$acceptQueryParams = true
	) {
		return new \Sta\Util\RangeUnit($this->getController(), $maxLength, $unit, $rawHeader, $acceptQueryParams);
	}

}