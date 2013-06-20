<?php
namespace Sta\Mvc\Controller\Plugin;

use Sta\Mvc\Controller\Plugin\RangeUnit\Bean;
use Sta\Mvc\Controller\Plugin\RangeUnit\Exception;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceManager;

/**
 * @author Stavarengo
 */
class RangeUnit extends AbstractPlugin
{

	/**
	 * Valida o conteúdo da entidade Range e se ela estiver correta, retorna suas informações.
	 *
	 * @param int $maxLength
	 *        Quantidade máxima que poderá ser retornado. Tamanho total da entidade.
	 *
	 * @param \Zend\Http\Header\HeaderInterface $rangeHeader
	 *        Default null. A entidade Range do cabeçalho da requisição.
	 *        Quando null será usado o valor retornado pelo método {@link getRawHeader() }.
	 *
	 * @throws RangeUnit\Exception
	 * @return Bean
	 *        Retorna null se $rangeHeader não for satisfatória.
	 *        Ex. para Range:"items=0-25" ou Range:"items="-25", o retorno será um objeto {@link Bean}
	 *        com os seguintes valores: start = 0, end = 25, length = 26 e unit = 'items'.
	 */
	public function get($maxLength, \Zend\Http\Header\HeaderInterface $rangeHeader = null)
	{
		$rangeHeader = ($rangeHeader ? $rangeHeader : $this->getRawHeader());

		if (!$this->isSatisfactory($maxLength, null, $rangeHeader)) {
			throw new Exception('Range "' . $rangeHeader->getFieldValue() . '" não é satisfatório.');
		}

		return $this->_getBean($maxLength, $rangeHeader);
	}

	/**
	 * Busca pela entidade Range ou X-Range no cabeçalho da requisição.
	 * Quando Range e X-Range estiver presente, Range prevalece.
	 * @return \Zend\Http\Header\HeaderInterface
	 */
	public function getRawHeader()
	{
		/** @var $request Request */
		$request = $this->getController()->getRequest();
		if (!($rangeHeader = $request->getHeader('Range'))) {
			// Se não encontrou a entidade Range, procura por X-Range
			$rangeHeader = $request->getHeader('X-Range');
		}
		return $rangeHeader;
	}

	/**
	 * Valida se $rawHeader é satisfatório.
	 *
	 * @param $maxLength
	 *        Tamanho total da entidade.
	 *
	 * @param $unit
	 *        Unidade de medida da entidade. Ex: items, bytes.
	 *
	 * @param \Zend\Http\Header\HeaderInterface $rawHeader
	 *        Opcional. Quando null será usado o valor retornado pelo método {@link getRawHeader()}.
	 *
	 * @return bool
	 *        Retorna true se o Range ou X-Range existir no cabeçalho HTTP da requisição e se o valor deste cabeçalhos
	 *        estiver no formato correto estabelecido em {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.35.1}.
	 *            Ex. de unidades válidas:
	 *            items=1-5
	 *            items=-5
	 *            items=1-
	 */
	public function isSatisfactory($maxLength, $unit, \Zend\Http\Header\HeaderInterface $rawHeader = null)
	{
		if (!$rawHeader) {
			if (!$rawHeader = $this->getRawHeader()) {
				return false;
			}
		}

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