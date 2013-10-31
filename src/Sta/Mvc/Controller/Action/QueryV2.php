<?php
namespace Sta\Mvc\Controller\Action;

use Doctrine\ORM\QueryBuilder;
use Sta\Mvc\Controller\Action;

/**
 * Extende {@link \Sta\Mvc\Controller\Action\Query} para criar uma classe que ja implementa a maior parte dos comportamentos
 * de uma ação de query.
 *
 * @author: Stavarengo
 */
abstract class QueryV2 extends Query
{

	/**
	 * @var QueryBuilder
	 */
	private $_queryBuildPrototype;

	/**
	 * @return QueryBuilder
	 */
	public abstract function getQueryBuild();

	/**
	 * Conta a quantidade máxima de registro que esta requisição poderia retornar se não fosse aplicado paginação.
	 * @return float
	 */
	public function count()
	{
		$qb          = $this->_getQueryBuildClone();
		$rootAliases = $qb->getRootAliases();
		$qb->select($qb->expr()->count($rootAliases[0]));
		return (int)$qb->getQuery()->getSingleScalarResult();
	}

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
	protected function fetchAll(array $sortDef, $count = null, $offset = null)
	{
		$query = $this->getQueryBuild()->getQuery();

		$query->setMaxResults($count);
		$query->setFirstResult($offset);
		
		$entidades = $query->getResult();
		$entidades = $this->getController()->entityToArray($entidades, array(
			'depth' => $this->getController()->getParam('depth'),
		));
		return $entidades;
	}

	/**
	 * @return QueryBuilder
	 */
	private function _getQueryBuildClone()
	{
		if (!$this->_queryBuildPrototype) {
			$this->_queryBuildPrototype = $this->getQueryBuild();
		}
		return clone $this->_queryBuildPrototype;
	}

}