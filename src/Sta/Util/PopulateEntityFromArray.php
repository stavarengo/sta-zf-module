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
        if (method_exists($serviceLocator, 'getServiceLocator')) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }
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
     *      Aceita as seguintes entradas
	 *        - autoPersist: Boolean (default true)
     *              Auto persist as entidades relacionadas no EntityManager. Não faz chamdas ao flush().
     *  
	 */
	private function _populate(array $entityData, AbstractEntity $entity, array $options = array())
	{
        $entityManager = $this->_getEm();

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
                $entityAssociated = null;
                if ($entityData[$field] instanceof AbstractEntity) {
                    $entityAssociated = $entityData[$field];
                } else {
                    $targetEntity  = $fieldDefinition['targetEntity'];
                    $associatedEntityOrId = $this->_getAssociationId($entityData, $field, $targetEntity, $options);
                    if ($associatedEntityOrId instanceof AbstractEntity) {
                        $entityAssociated = $associatedEntityOrId;
                    } else if ($associatedEntityOrId !== null) {
                        if (!$entityAssociated = $entityManager->find($targetEntity, $associatedEntityOrId)) {
                            throw new PopulateEntityFromArrayException('Valor do atributo do atributo "' . $field . '" não é ' .
                                'válido. Não existe uma uma entidade "' . $targetEntity . '" com ID "' . $associatedEntityOrId . '".');
                        }
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
                $originalValue = $value;
                $value = self::strToDateTime($this->getServiceLocator(), $value, $fieldType);
                if (!$value) {
                    $config      = $this->getServiceLocator()->get('config');
                    $fieldFormat = $config['web']['datetime'][$fieldType];
                    $agora       = new \DateTime('now');
                    
                    throw new PopulateEntityFromArrayException(
                        'Formato de data/hora inválido para o atributo "'
                        . $fieldName . '". Valo recebido: "' . $originalValue . '". Formato esperado: "' . $fieldFormat
                        . '" - Ex: ' . $agora->format($fieldFormat) . '.', 400
                    );
                }
			}
		}

		$entity->set($fieldName, $value);
	}

	private function _getAssociationId($entityData, $associationField, $targetEntityClassName, array $options)
	{
		$associationData = $entityData[$associationField];
		$entityName      = basename(str_replace('\\', DIRECTORY_SEPARATOR, $targetEntityClassName));
		if (is_array($associationData)) {
            $theData = $associationData;
            if (array_key_exists($entityName, $associationData)) {
                $theData = $associationData[$entityName];
            }
            $associationEntity = null;
			if (array_key_exists('id', $theData)) {
                if ($associationEntity = $this->_getEm()->find($targetEntityClassName, $theData['id'])) {
                    unset($theData['id']);
                }
			}
            if (!$associationEntity) {
                $associationEntity = new $targetEntityClassName;
                if ($this->_getOption($options, 'autoPersist', true)) {
                    $this->_getEm()->persist($associationEntity);
                }
            }
            
            $this->_populate($theData, $associationEntity, $options);
            return $associationEntity;
		}
		return $associationData;
	}

    /**
     * @return EntityManager
     */
    private function _getEm()
    {
        /** @var $entityManager EntityManager */
        $entityManager = $this->serviceManager->get('Doctrine\ORM\EntityManager');

        return $entityManager;
    }

    private function _getOption(array $options, $optionName, $default)
    {
        return (array_key_exists($optionName, $options) ? $options[$optionName] : $default);
    }

    /**
     * @param $fieldName
     * @param $value
     * @param $fieldType
     *
     * @return \DateTime
     * @throws \Sta\Util\PopulateEntityFromArrayException
     */
    public static function strToDateTime(ServiceLocatorInterface $sl, $value, $fieldType)
    {
        $config      = $sl->get('config');
        $defTz       = new \DateTimeZone(date_default_timezone_get());
        $fieldFormat = $config['web']['datetime'][$fieldType];

        $originalValue = $value;
        if (!($value = \DateTime::createFromFormat($fieldFormat, $value))) {
            // Se falhou ao converter a data, talvez seja pq enviou um formato completo de data para quando o 
            // campo aceita apenas parte da data.
            // Ex: enviou data e hroa quando o campo aceita apenas data ou hora.
            // Neste caso abaixo damos mais uma chance tentando descobrir se foi isso que aconteceu e usando
            // a formatação mais adequada para o valor recebido.
            $agora                = new \DateTime('now', $defTz);
            $dateTimeStrLength    = strlen($agora->format($config['web']['datetime']['datetime']));
            $lengthDaDataRecebida = strlen($originalValue);
            if ($lengthDaDataRecebida == $dateTimeStrLength) {
                if ($fieldType == 'time' || $fieldType == 'date') {
                    // o campo aceita apenas data ou hora, mas recebemos um valor com a mesma qtd de chars
                    // usado para campos que aceitam data e hora. Talvez, o valor recebido soh esteja no fomato
                    // completo quando deveria estar no formato menor, como descrito no comentario acima.
                    // Vamos dar mais uma chance!
                    $value = \DateTime::createFromFormat($config['web']['datetime']['datetime'], $originalValue);
                }
            }
            if (!$value) {
                return null;
            }
        }
        $value->setTimezone($defTz);

        return $value;
    }
}
