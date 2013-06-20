<?php
namespace Sta\Util;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sta\Entity\AbstractEntity;
use Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\DispatchableInterface;

/**
 * @author: Stavarengo
 */
class PopulateEntityFromArray implements PluginInterface, ServiceLocatorAwareInterface
{

	/**
	 * @var ServiceLocatorInterface
	 */
	public $serviceManager;

	public function __invoke(array $data, AbstractEntity $entity)
	{
		$this->populate($data, $entity);
	}

	/**
	 * @param array $data
	 * @param AbstractEntity $entity
	 */
	public function populate(array $data, AbstractEntity $entity)
	{
		$this->_populate($data, $entity);
	}

	/**
	 * Set the current controller instance
	 *
	 * @param \Sta\Util\Dispatchable|\Zend\Stdlib\DispatchableInterface $controller
	 *
	 * @return void
	 */
	public function setController(DispatchableInterface $controller)
	{
	}

	/**
	 * Get the current controller instance
	 *
	 * @return null|Dispatchable
	 */
	public function getController()
	{
	}

	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceManager = $serviceLocator;
	}

	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 * @return \Zend\ServiceManager\ServiceLocatorInterface
	 */
	public function getServiceLocator()
	{
		return $this->serviceManager;
	}

	/**
	 * @param array $data
	 * @param \Sta\Entity\AbstractEntity $entity
	 */
	private function _populate(array $data, AbstractEntity $entity)
	{
		/** @var $entityManager EntityManager */
		$entityManager = $this->serviceManager->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$classMetadata = $entityManager->getClassMetadata(get_class($entity));
		$fieldMappings = $classMetadata->fieldMappings;
		foreach ($fieldMappings as $field => $fieldDefinition) {
			if (array_key_exists($field, $data)) {
				$this->_setValueToEntity($entity, $field, $fieldDefinition, $data[$field]);
			}
		}
		$associationMappings = $classMetadata->associationMappings;
		foreach ($associationMappings as $field => $fieldDefinition) {
			if (array_key_exists($field, $data)) {
				$targetEntity = $fieldDefinition['targetEntity'];
				$qb = $entityManager->getRepository($targetEntity)->createQueryBuilder('a');
				$qb->select('a');
				$qb->where('a.id = ?1');
				$qb->setParameter(1, $data[$field]);
				$q                = $qb->getQuery();
//				$q->setFetchMode($targetEntity, $field,  ClassMetadata::FETCH_EAGER);
//				$q->setHydrationMode(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD);
//				$q->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true);
				// @TODO Tem que fazer esta query buscar apenas o ID do objeto para melhorar a perfomance
				$entityAssociated = $q->getOneOrNullResult();
				$this->_setValueToEntity($entity, $field, $fieldDefinition, $entityAssociated);
			}
		}
	}

	private function _setValueToEntity(AbstractEntity $entity, $fieldName, array $fieldDefinition, $value)
	{
		if ($value) {
			$fieldType     = $fieldDefinition['type'];
			$datetimeTypes = array('datetime', 'date', 'time');
			if (in_array($fieldType, $datetimeTypes)) {
				$config        = $this->serviceManager->getServiceLocator()->get('config');
				$defTz         = $config['webapp']['datetime']['deftault-timezone'];
				$fieldFormat   = $config['webapp']['datetime'][$fieldType];
				$originalValue = $value;
				if (!($value = \DateTime::createFromFormat($fieldFormat, $value))) {
					throw new PopulateEntityFromArrayException('Formato de data/hora inválido para o atributo "'
					. $fieldName . '". Valo recebido: "' . $originalValue . '". Formato esperado: "' . $fieldFormat
					. '".');
				}
				$value->setTimezone($defTz);
			}
		}

		$entity->set($fieldName, $value);
	}
}