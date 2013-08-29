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
	 * @throws InvalidArgument
	 * @return bool
	 */
	public function isValid($value)
	{
		if (!$value instanceof RegistroDuplicadoValue) {
			throw new \Sta\Entity\Validator\InvalidArgument();
		}

		/** @var $em EntityManager */
		$em               = \Sta\Module::getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$entity           = $value->entity;
		$entityClass      = get_class($entity);
		$entityRepository = $em->getRepository($entityClass);
		$qb = $entityRepository->createQueryBuilder('a');

		$count = 0;
		foreach ($value->attributes as $attributeName) {
			$attributeValue = $entity->get($attributeName);

			if ($attributeValue instanceof AbstractEntity) {
				if (!$em->getRepository(get_class($attributeValue))->find((int)$attributeValue->getId())) {
					// Está verificando a duplicidade de valores em uma coluna FK (que aponta para outra tabela),
					// porém o registro da outra tabela ainda não existe, então com certesa esta coluna não está duplicada.
					// O regsitro da outra tabela pode não existir quando ainda não foi invocado EntityManager::flush().
					return true;
				}
			}

			$qb->andWhere("a." . $attributeName . " = :attributeValue" . (++$count));
			$qb->setParameter('attributeValue' . $count, $attributeValue);
		}

		if ($entity->getId()) {
			$qb->andWhere('a.id <> :id');
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