<?php
namespace Sta\Entity\Validator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Sta\Entity\AbstractEntity;

class RegistroDuplicado extends \Zend\Validator\AbstractValidator
{

	const ENTIDADE_DUPLICADA = 'entidadeDuplicada';
	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $messageTemplates = array(
		self::ENTIDADE_DUPLICADA => "Registro duplicado",
	);

	/**
	 * @param RegistroDuplicadoValue $value
	 *
	 * @throws Exception
	 * @return bool
	 */
	public function isValid($value)
	{
		if (!$value instanceof RegistroDuplicadoValue) {
			throw new \Sta\Entity\Validator\InvalidArgument();
		}

		$entity           = $value->entity;
		$entityClass      = get_class($entity);
		/** @var $em EntityManager */
		$em               = \Sta\Module::getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$attributeName = $value->attribute;
		$attributeValue = $entity->get($attributeName);

		if ($attributeValue instanceof AbstractEntity) {
			if (!$em->getRepository(get_class($attributeValue))->find((int)$attributeValue->getId())) {
				// Está verificando a duplicidade de valores em uma coluna FK (que aponta para outra tabela),
				// porém o registro da outra tabela ainda não existe, então com certeza esta coluna não está duplicada.
				// O regsitro da outra tabela pode não existir quando ainda não foi invocado EntityManager::flush().
				return true;
			}
		}

		$entityRepository = $em->getRepository($entityClass);
		$qb = $entityRepository->createQueryBuilder('a');

		$qb->where("a.$attributeName = :attributeValue");
		$qb->setParameter('attributeValue', $attributeValue);

		if ($entity->getId()) {
			$qb->where('a.id <> :id');
			$qb->setParameter('id', $entity->getId());
		}

		$qb->setMaxResults(1);

		if ($qb->getQuery()->getOneOrNullResult() !== null) {
			$this->error(self::ENTIDADE_DUPLICADA);
			return false;
		}

		return true;
	}
}