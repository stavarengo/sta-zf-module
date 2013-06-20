<?php
namespace Sta\Mvc\Controller\Action;

use Sta\Mvc\Controller\Action;

/**
 * Implementação base para ações que podem servir resultados paginados através do uso da entidade Range do HTTP.
 *
 * Esta classe aceita requisições com os seguintes parâmetros
 *    depth: integer (opcional)
 * 		  Quão profundo serão retorno os itens associados a entidade principal.
 *    sort: string|string[] (opcional)
 *        Determina a ordenação dos resultados.
 *        Ex: "nome desc"
 *            "-nome"
 *            "+nome"
 *            "nome asc,campo2 desc[,...]"
 *            "+nome,-campo2[,...]"
 *            array('nome desc', ...)
 *            array('-nome', ...)
 *            array('nome' => 'desc' || 'asc', ...)
 *
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.35.2
 * @author Stavarengo
 */
abstract class Query extends Action
{

	/**
	 * Nome da unidade aceitável para entidade Range.
	 * @var string
	 */
	protected $rangeUnit = 'items';
	/**
	 * Quantidade máxima de registros que esta ação retorna.
	 * Uma requisição que retorne mais registros do que este valor vai resultar na resposta "413 - Request entity too large".
	 * @var float
	 */
	protected $maxLength = 100;
	/**
	 * @var array
	 */
	private $_sortDef = null;

	/**
	 * Conta a quantidade máxima de registro que esta requisição poderia retornar se não fosse aplicado paginação.
	 * @return float
	 */
	public abstract function count();

	/**
	 * Retorna os registros desta requisição.
	 *
	 * @param array $sortDef
	 *        Terá o mesmo formado do retorno do método {@link Query::getSortDef() }
	 * @param int $count
	 * @param int $offset
	 *
	 * @return array
	 */
	protected abstract function fetchAll(array $sortDef, $count = null, $offset = null);

	/**
	 * @return \Sta\Mvc\Controller\mixin|\Sta\Mvc\Controller\Plugin\GetConfiguredResponse
	 */
	public function execute()
	{
		$rowCount = $this->count();

		if (!$this->getController()->rangeUnit()->isSatisfactory($rowCount, $this->rangeUnit)) {
			//416 - Requested range not satisfiable
			$responseHeaders = array('Content-Range' => $this->rangeUnit . ' */' . ($rowCount - 1));
			return $this->getController()->getConfiguredResponse(416, null, $responseHeaders);
		}
		$range = $this->getController()->rangeUnit()->get($rowCount);

		if ($range->getLength() > $this->maxLength) {
			//413 - Request entity too large
			$msg = "Nós não enviamos mais do que $this->maxLength registros por requisição.";
			return $this->getController()->getConfiguredResponse(416, $msg);
		}

		try {
			$retorno = $this->fetchAll($this->getSortDef(), $range->getLength(), $range->getStart());
		} catch (\Exception $e) {
			return $this->getController()->getConfiguredResponse(400, $e->getMessage());
		}

		$responseHeaders = array('Content-Range' => "items {$range->getStart()}-{$range->getEnd()}/$rowCount");
		if ($rowCount > count($retorno)) {
			$codigo = 206; //Estou paginando o resultado da consulta
		} else {
			$codigo = 200; //Estou retornando todos os registros possíveis para esta consulta
		}

		return $this->getController()->getConfiguredResponse($codigo, $retorno, $responseHeaders);
	}

	/**
	 * Retorna a especificação de ordenação dos resultados.
	 * @return SortDef[]
	 *        Um array com a seguinte estrutura
	 *            array('nome_campo' => tipo_ordenacao)
	 *        O "tipo_ordenacao" será do tipo {@link SortDef }
	 */
	protected function getSortDef()
	{
		if ($this->_sortDef !== null) return $this->_sortDef;

		$sort           = $this->getController()->params()->fromQuery('sort');
		$this->_sortDef = array();

		if ($sort === null) return $this->_sortDef;

		try {
			$sortDecoded = \Zend\Json\Json::decode($sort, \Zend\Json\Json::TYPE_ARRAY);
			if ($sortDecoded) $sort = $sortDecoded;
		} catch (\Exception $e) {
			// Se falhar é porque o sort é uma string pura.
		}

		if ($sort === null) return $this->_sortDef;

		if (is_string($sort)) {
			$sort = explode(',', $sort);
		}
		foreach ($sort as $campo => $tipo) {
			if (is_int($campo)) {
				$campo = $tipo;
				$tipo  = null;
			}
			if ($tipo === null) {
				if (strpos($campo, '-') === 0) {
					$tipo = '-';
				} else if (strpos($campo, '+') === 0 || strpos($campo, ' ') === 0) {
					//Acima estou considerando o espaço vazio como '+' porque o caracter '+' é tratado como caracter especial
					//e por conta disto o servidor recebe um espaço em branco no lugar dele.
					$tipo = '+';
				}
				if ($tipo) {
					//Se $tipo possuir conteúdo significa que o nome do campo começa com um dos
					//caracteres de ordenação, + ou -, por isso agora estes caracteres serão removidos.
					$campo = substr($campo, 1);
				}
			}

			$tipo                   = strtolower($tipo);
			$desc                   = ($tipo == 'descending' || $tipo == 'd' || $tipo == 'des' || $tipo == 'desc' || $tipo == '-');
			$this->_sortDef[$campo] = ($desc ? SortDef::$DESC : SortDef::$ASC);
		}

		return $this->_sortDef;
	}

}