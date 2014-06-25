<?php
namespace Sta\Util;


use Doctrine\ORM\EntityManager;
use Sta\Entity\AbstractEntity;
use Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
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

	public function __invoke(array $data, AbstractEntity $entity, array $options = array())
	{
		$this->populate($data, $entity, $options);
	}

	/**
	 * @param array $data
	 * @param AbstractEntity $entity
	 * @param array $options
	 *        Veja as opções válidas em {@link \Sta\Util\PopulateEntityFromArray::_populate()}
	 */
	public function populate(array $data, AbstractEntity $entity, array $options = array())
	{
		$this->_populate($data, $entity, $options);
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
	 * @param array $entityData
	 *        Os dados da entidade no formato array.
	 * @param \Sta\Entity\AbstractEntity $entity
	 *        A instância da entidade que será populada.
	 * @param array $options
	 *        Ainda não aceita nenhuma opção. Foi adicionado este parâmetro para uso futuro.
	 */
	private function _populate(array $entityData, AbstractEntity $entity, array $options = array())
	{
		/** @var $entityManager EntityManager */
		$entityManager = $this->serviceManager->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $thisEntityClass = \App\Entity\AbstractEntity::getClass($entity);
        $classMetadata = $entityManager->getClassMetadata($thisEntityClass);
		$fieldMappings = $classMetadata->fieldMappings;
		foreach ($fieldMappings as $field => $fieldDefinition) {
			if (array_key_exists($field, $entityData)) {
				$this->_setValueToEntity($entity, $field, $fieldDefinition, $entityData[$field]);
			}
		}
		$associationMappings = $classMetadata->associationMappings;
		foreach ($associationMappings as $field => $fieldDefinition) {
			if (array_key_exists($field, $entityData)) {
				$targetEntity  = $fieldDefinition['targetEntity'];
				$associationId = $this->_getAssociationId($entityData, $field, $targetEntity, $options);
                $entityAssociated = null;
                if ($associationId !== null) {
                    $qb = $entityManager->getRepository($targetEntity)->createQueryBuilder('a');
                    $qb->select('a');
                    $qb->where('a.id = ?1');
                    $qb->setParameter(1, $associationId);
                    $q = $qb->getQuery();
    //				$q->setFetchMode($targetEntity, $field,  ClassMetadata::FETCH_EAGER);
    //				$q->setHydrationMode(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD);
    //				$q->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true);
                    // @TODO Tem que fazer esta query buscar apenas o ID do objeto para melhorar a perfomance
                    if (!$entityAssociated = $q->getOneOrNullResult()) {
                        throw new PopulateEntityFromArrayException('Valor do atributo do atributo "' . $field . '" não é ' .
                            'válido. Não existe uma uma entidade "' . $targetEntity . '" com ID "' . $associationId . '".');
                    }
                }
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
				$config      = $this->serviceManager->getServiceLocator()->get('config');
				$defTz       = new \DateTimeZone($config['webapp']['datetime']['deftault-timezone']);
				$fieldFormat = $config['webapp']['datetime'][$fieldType];

				$originalValue = $value;
				if (!($value = \DateTime::createFromFormat($fieldFormat, $value))) {
					// Se falhou ao converter a data, talvez seja pq enviou um formato completo de data para quando o 
					// campo aceita apenas parte da data.
					// Ex: enviou data e hroa quando o campo aceita apenas data ou hora.
					// Neste caso abaixo damos mais uma chance tentando descobrir se foi isso que aconteceu e usando
					// a formatação mais adequada para o valor recebido.
					$agora                = new \DateTime('now', $defTz);
					$dateTimeStrLength    = strlen($agora->format($config['webapp']['datetime']['datetime']));
					$lengthDaDataRecebida = strlen($originalValue);
					if ($lengthDaDataRecebida == $dateTimeStrLength) {
						if ($fieldType == 'time' || $fieldType == 'date') {
							// o campo aceita apenas data ou hora, mas recebemos um valor com a mesma qtd de chars
							// usado para campos que aceitam data e hora. Talvez, o valor recebido soh esteja no fomato
							// completo quando deveria estar no formato menor, como descrito no comentario acima.
							// Vamos dar mais uma chance!
							$value = \DateTime::createFromFormat($config['webapp']['datetime']['datetime'], $originalValue);
						}
					}
					if (!$value) {
						throw new PopulateEntityFromArrayException('Formato de data/hora inválido para o atributo "'
							. $fieldName . '". Valo recebido: "' . $originalValue . '". Formato esperado: "' . $fieldFormat
							. '" - Ex: ' . $agora->format($fieldFormat) . '.');
					}
				}
				$value->setTimezone($defTz);
			}
		}

		$entity->set($fieldName, $value);
	}

	private function _getAssociationId($entityData, $associationField, $targetEntityClassName, array $options)
	{
		$associationData = $entityData[$associationField];
		$entityName      = basename(str_replace('\\', DIRECTORY_SEPARATOR, $targetEntityClassName));
		if (is_array($associationData) && array_key_exists($entityName, $associationData)) {
			if (array_key_exists('id', $associationData[$entityName])) {
				return $associationData[$entityName]['id'];
			}
		}
		return $associationData;
	}
}