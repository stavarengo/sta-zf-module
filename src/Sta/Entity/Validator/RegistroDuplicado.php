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
	 * @var RegistroDuplicadoValue
	 */
	protected $value;
	/**
	 * @var $em EntityManager
	 */
	private $em;
	/**
	 * @var AbstractEntity
	 */
	private $entity;
	/**
	 * @var string
	 */
	private $entityClass;

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

		$this->em          = \Sta\Module::getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$this->value       = $value;
		$this->entity      = $value->entity;
		$this->entityClass = get_class($this->entity);

		return $this->chekInsertionScheduled() && $this->checkDatabase();


	}

	/**
	 * Verifica se existe outras entidades iguais a esta na fila de entidades para inserção.
	 * As entidades que estão agendadas para inserção, ainda não existem no banco de dados, por tanto, uma verificação
	 * comum ao banco de dados não acusaria a duplicidade.
	 *
	 * Ex:
	 *        // Cria duas entidades - assuma que elas estão duplicadas
	 *        $e1 = new Entidade();
	 *        $e1->setCpf('11111111111');
	 *        $e2 = new Entidade();
	 *        $e2->setCpf('11111111111');
	 *        $em =  getEntityManager();
	 *        $em->persist($e1);
	 *        $em->persist($e2);
	 *        $em->flush();
	 *
	 * No exemplo acima, as duas entidades duplicadas não existem no banco de dados, por tanto, para identificar esta
	 * duplicidade é necessário verificar na fila de inserção.
	 *
	 * @return bool
	 */
	private function chekInsertionScheduled()
	{
		$uow                       = $this->em->getUnitOfWork();
		$scheduledEntityInsertions = $uow->getScheduledEntityInsertions();
		/** @var $sei AbstractEntity */
		foreach ($scheduledEntityInsertions as $sei) {
			if ($sei !== $this->entity && $sei instanceof $this->entityClass) {
				$estaDuplicado = false;
				foreach ($this->value->attributes as $attributeName) {
					$attributeValue       = $this->entity->get($attributeName);
					$otherEntityAttrValue = $sei->get($attributeName);
					// @TODO Aprimorar a comparacao de campos dada/hora
					// @TODO Aprimorar a comparacao de campos cujo valor é um objeto, mas não instancia de AbstractEntity
					if ($attributeValue instanceof AbstractEntity) {
						$estaDuplicado = ($attributeValue === $otherEntityAttrValue);
					} else if ($attributeValue == $otherEntityAttrValue) {
						$estaDuplicado = true;
					} else {
						$estaDuplicado = false;
					}
					
					if (!$estaDuplicado) {
						// Se pelo menos um dos atributos testados não estiver duplicado, então não podemos considerar
						// a entidade como duplicada, e por isso nem precisamos continuar verificando os outros atributos.
						break;
					}
				}

				if ($estaDuplicado) {
					$this->error(self::ENTIDADE_DUPLICADA);
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Verifica duplicidade da entidade com os registros que já existem no banco de dados.
	 *
	 * @return bool
	 */
	private function checkDatabase()
	{
		$entityRepository = $this->em->getRepository($this->entityClass);
		$qb               = $entityRepository->createQueryBuilder('a');
		$count            = 0;
		foreach ($this->value->attributes as $attributeName) {
			$attributeValue = $this->entity->get($attributeName);

			if ($attributeValue instanceof AbstractEntity) {
				if (!$this->em->getRepository(get_class($attributeValue))->find((int)$attributeValue->getId())) {
					// Está verificando a duplicidade de valores em uma coluna FK (que aponta para outra tabela),
					// porém o registro da outra tabela ainda não existe, então com certesa esta coluna não está duplicada.
					// O regsitro da outra tabela pode não existir quando ainda não foi invocado EntityManager::flush().
					return true;
				}
			}

			$qb->andWhere("a." . $attributeName . " = :attributeValue" . (++$count));
			$qb->setParameter('attributeValue' . $count, $attributeValue);
		}

		
		$notInIds = array();
		if ($this->entity->getId()) {
			$notInIds[] = $this->entity->getId();
		}

		// Desconsidera as possiveis entidades duplicadas que foram agendadas para exlusão 
		$scheduledEntityDeletions = $this->em->getUnitOfWork()->getScheduledEntityDeletions();
		foreach ($scheduledEntityDeletions as $sed) {
			if ($sed instanceof $this->entityClass) {
				if ($sed->getId()) {
					$notInIds[] = $sed->getId();
				}
			}
		}
		if ($notInIds) {
			if (count($notInIds) == 1) {
				$qb->andWhere($qb->expr()->neq('a.id', ':id'));
				$notInIds = reset($notInIds);
			} else {
				$qb->andWhere($qb->expr()->notIn('a.id', ':id'));
			}
			$qb->setParameter('id', $notInIds);
		}

		$qb->setMaxResults(1);

		if ($qb->getQuery()->getOneOrNullResult() !== null) {
			$this->error(self::ENTIDADE_DUPLICADA);
			return false;
		}

		return true;
	}
	
}