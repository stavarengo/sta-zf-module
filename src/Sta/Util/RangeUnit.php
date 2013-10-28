<?php
namespace Sta\Util;

use Sta\Mvc\Controller\AbstractActionController;
use Sta\Util\RangeUnit\Bean;
use Sta\Util\RangeUnit\Exception;
use Zend\Http\Header\Range;
use Zend\Http\PhpEnvironment\Request;

/**
 * @author Stavarengo
 */
class RangeUnit
{

	/**
	 * @var \Sta\Mvc\Controller\AbstractActionController
	 */
	private $controller;
	/**
	 * @var int
	 */
	private $maxLength;
	/**
	 * @var string
	 */
	private $unit;
	/**
	 * @var \Zend\Http\Header\HeaderInterface
	 */
	private $rawHeader;
	/**
	 * @var bool
	 */
	private $acceptQueryParams;

	/**
	 *
	 * @param \Sta\Mvc\Controller\AbstractActionController $controller
	 *
	 * @param int $maxLength
	 *        Quantidade máxima permitida para o range. Tamanho total da entidade.
	 *          Este valor determina
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
	 */
	public function __construct(AbstractActionController $controller, $maxLength, $unit = 'items',
		\Zend\Http\Header\HeaderInterface $rawHeader = null, $acceptQueryParams = true
	) {
		$this->controller        = $controller;
		$this->maxLength         = $maxLength;
		$this->unit              = $unit;
		$this->rawHeader         = ($rawHeader === null ? false : $rawHeader);
		$this->acceptQueryParams = $acceptQueryParams;
	}

	/**
	 * Valida o conteúdo da entidade Range e se ela estiver correta retorna suas informações.
	 *
	 * @throws RangeUnit\Exception
	 * @return Bean
	 *        Retorna null se o {@link getRawHeader() Range header} não for satisfatório.
	 *        Ex. para Range:"items=0-25" ou Range:"items="-25", o retorno será um objeto {@link Bean}
	 *        com os seguintes valores: start = 0, end = 25, length = 26 e unit = 'items'.
	 */
	public function get()
	{
		$rangeHeader = $this->getRawHeader();

		if (!$this->isSatisfactory()) {
			throw new Exception('Range "' . $rangeHeader->getFieldValue() . '" não é satisfatório.');
		}

		return $this->_getBean($this->maxLength, $rangeHeader);
	}

	/**
	 * Busca pela entidade Range ou X-Range no cabeçalho da requisição.
	 * Quando Range e X-Range estiver presente, Range prevalece.
	 *
	 * Se não foi definido um range no cabeçalho e se {@link acceptQueryParams } for true, tentamos montar um range
	 * usando os parâmetros 'start' e 'count' da URL.
	 *
	 * Quando nenhum range for definido, nós tentaremos retornar todas as entidades.
	 *
	 * @return \Zend\Http\Header\HeaderInterface
	 */
	public function getRawHeader()
	{
		if ($this->rawHeader === false) {
			/** @var $request Request */
			$request = $this->controller->getRequest();
			if (!($rangeHeader = $request->getHeader('Range'))) {
				// Se não encontrou a entidade Range, procura por X-Range
				$rangeHeader = $request->getHeader('X-Range');
			}

			if (!$rangeHeader) {
				// Se a entidade Range não foi envida, monta um range que permite retornar todas as entidades.
				$rangeUnit = $this->unit;
				$start     = 0;
				$count     = $this->maxLength;

				if ($this->acceptQueryParams) {
					$start = $this->controller->params()->fromQuery('start', $start);
					$count = $this->controller->params()->fromQuery('count', $count);
				}

				$end         = ($start + ($count > 0 ? ($count - 1) : 0));
				$rangeHeader = Range::fromString("Range: $rangeUnit=$start-$end");
			}

			$this->rawHeader = $rangeHeader;
		}

		return $this->rawHeader;
	}

	/**
	 * Valida se o range retornado por {@link getRawHeader() } é satisfatório.
	 *
	 * @return bool
	 *        Retorna true se o range estiver no formato correto estabelecido em {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.35.1}.
	 *            Ex. de unidades válidas:
	 *            items=1-5
	 *            items=-5
	 *            items=1-
	 */
	public function isSatisfactory()
	{
		if (!$rawHeader = $this->getRawHeader()) {
			return false;
		}

		$unit      = $this->unit;
		$maxLength = $this->maxLength;
		$raw       = $rawHeader->getFieldValue();
		$regexUnit = ($unit ? $unit : '[a-zA-Z]+');
		if ($raw && preg_match("/^$regexUnit=\d*-\d*(,\d*-\d*)*$/", $raw)) {
			$range = $this->_getBean($maxLength, $rawHeader);
			if ($range->getStart() <= $range->getEnd() && $range->getStart() <= ($maxLength > 0 ? ($maxLength - 1) : 0)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $maxLength
	 *
	 * @param \Zend\Http\Header\HeaderInterface $rangeHeader
	 *
	 * @return Bean
	 */
	private function _getBean($maxLength, \Zend\Http\Header\HeaderInterface $rangeHeader)
	{
		$rangeHeader = $rangeHeader->getFieldValue();

		$ranges = explode('=', $rangeHeader);
		$unit   = $ranges[0];
		$parts  = explode('-', $ranges[1]);
		$start  = (int)$parts[0]; // If this is empty, this should be 0.
		//$start  = ($start > $maxLength - 1 ? $maxLength - 1 : $start);
		$end = (int)($parts[1] === '' ? $maxLength - 1 : $parts[1]);

		return new Bean($start, $end, $unit);
	}

}