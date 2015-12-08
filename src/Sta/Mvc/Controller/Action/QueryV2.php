<?php
namespace Sta\Mvc\Controller\Action;

use App\Entity\AbstractEntity;
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
		$qb->select($qb->expr()->countDistinct($rootAliases[0]));
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
		$entidades = $this->fetchAllEntities($sortDef, $count, $offset);
		$entidades = $this->getController()->entityToArray($entidades, array(
			'depth' => $this->getController()->getParam('depth'),
			'noEntityName' => $this->getController()->getParam('noEntityName', false),
		));
		return $entidades;
	}
    
	/**
	 * É o mesmo que {@link #fetchAll() }, porem retorna as entidades em forma de objeto ao inves de converter para 
     * array.
     * 
     * Este é apenas um método helper para que as subclasses possam usar as entidades retornadas antes delas serem 
     * convertidas para um array pelo método {@link #fetchAll() }. 
	 *
	 * @param array $sortDef
	 *        Terá o mesmo formado do retorno do método {@link Query::getSortDef() }
	 * @param int $count
	 * @param int $offset
	 *
	 * @return AbstractEntity[]
	 */
	protected function fetchAllEntities(array $sortDef, $count = null, $offset = null)
	{
		$query = $this->getQueryBuild()->getQuery();

		$query->setMaxResults($count);
		$query->setFirstResult($offset);
		
		return $query->getResult();
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