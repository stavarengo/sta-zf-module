<?php
namespace Sta\Entity\Validator;

use App\Entity\Annotation\SharingForbidden;
use App\Entity\Annotation\WithCompanyOwner;
use App\Entity\Company;
use App\Env\Env;
use App\Model\CompartilhamentosEmpresas;
use Doctrine\ORM\EntityManager;
use Sta\Entity\AbstractEntity;
use Sta\Entity\AbstractEntityWithId;

class RegistroDuplicado extends \Zend\Validator\AbstractValidator
{

    const DUPLICATE_ENTITY_WITH_COMPANIES = 'DUPLICATE_ENTITY_WITH_COMPANIES';
    const DUPLICATED_ENTITY = 'DUPLICATED_ENTITY';
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::DUPLICATE_ENTITY_WITH_COMPANIES => 'Duplicate record. There is already another entity "%entityName%" with values​: %valores%​​. Considering companies: %empresasConsideradas%',
        self::DUPLICATED_ENTITY => 'Duplicate record. There is already another entity "%entityName%" with values​: %valores%​​.',
    ];
    /**
     * @var RegistroDuplicadoValue
     */
    protected $value;
    /**
     * @var array
     */
    protected $messageVariables = [
        'valores' => 'valores',
        'entityName' => 'entityName',
        'empresasConsideradas' => 'empresasConsideradas',
    ];
    /**
     * @var string
     */
    protected $valores;
    /**
     * @var string
     */
    protected $empresasConsideradas;
    /**
     * @var string
     */
    protected $entityName;
    /**
     * Usado apenas quando a entidade validada foi anotada com {@link \App\Entity\Annotation\WithCompanyOwner}.
     * Deve armazenar o ID das empresas que compartilham esta entidade, iclusive o ID da company da company
     * relacionada com a entidade validada.
     *
     * @var array
     */
    protected $idDasEmpresasCompartilhando;
    /**
     * @var WithCompanyOwner
     */
    private $annoWithCompany;
    /**
     * @var $em EntityManager
     */
    private $em;
    /**
     * @var AbstractEntityWithId
     */
    private $entity;
    /**
     * @var string
     */
    private $entityClass;
    /**
     * @var SharingForbidden
     */
    private $sharingForbidden;

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

        /** @var Env $env */
        $env               = \Sta\Module::getServiceLocator()->get('App\Env');
        $this->em          = \Sta\Module::getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $this->value       = $value;
        $this->entity      = $value->entity;
        $this->entityClass = \App\Entity\AbstractEntity::getClass($this->entity);
        $this->entityName  = $this->entityClass;

        $refClass                          = \Sta\ReflectionClass::factory($value->entity, $env->isDev());
        $this->annoWithCompany             = $refClass->getClassAnnotation('App\Entity\Annotation\WithCompanyOwner');
        $this->sharingForbidden            = $refClass->getClassAnnotation('App\Entity\Annotation\SharingForbidden');
        $this->idDasEmpresasCompartilhando = [];
        $empresasConsideradas              = [];
        if ($this->annoWithCompany && !$this->sharingForbidden) {
            // Se a entidade aceita compartilhamentos, buscamos as empresas que compartilham esta entidade para
            // garantir que o registro não será duplicado mesmo entre as diferentes empresas que participam do
            // sharing.

            /** @var $modelCompartilhamentosEmpresas CompartilhamentosEmpresas */
            $modelCompartilhamentosEmpresas = \Sta\Module::getServiceLocator()->get('Model\SharesCompanies');

            $empresasQueCompartilhamEstaEntidade = [];
            $empresaProprietaria                 = $value->entity->get($this->annoWithCompany->attrName);
            if ($empresaProprietaria) {
                $empresasQueCompartilhamEstaEntidade = $modelCompartilhamentosEmpresas->getCompaniesThatShareThisEntityWithMe(
                    $empresaProprietaria,
                    $this->entityClass
                );
            }
            /** @var $row Company */
            foreach ($empresasQueCompartilhamEstaEntidade as $row) {
                $this->idDasEmpresasCompartilhando[] = $row->getId();
                $empresasConsideradas[]              = $row->getId() . '-' . $row->getPerson()->getFantasiaOuRazao();
            }
        }
        $this->empresasConsideradas = implode(', ', $empresasConsideradas);

        $valores = [];
        foreach ($this->value->attributes as $attributeName) {
            $attributeValue = $this->entity->get($attributeName);
            if ($attributeValue instanceof AbstractEntityWithId) {
                $attributeValue = $attributeValue->getId();
            }
            $valores[] = "'$attributeName'='$attributeValue'";
        }
        $this->valores = implode(', ', $valores);

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
        /** @var $sei AbstractEntityWithId */
        foreach ($scheduledEntityInsertions as $sei) {
            if ($sei !== $this->entity && $sei instanceof $this->entityClass) {
                $estaDuplicado = false;
                foreach ($this->value->attributes as $attributeName) {
                    $attributeValue       = $this->entity->get($attributeName);
                    $otherEntityAttrValue = $sei->get($attributeName);
                    // @TODO Aprimorar a comparacao de campos dada/hora
                    // @TODO Aprimorar a comparacao de campos cujo valor é um objeto, mas não instancia de AbstractEntity
                    if ($attributeValue instanceof AbstractEntity) {
                        $estaDuplicado = ($attributeValue === $otherEntityAttrValue && $this->pertenceAoCompartilhamento(
                                $sei
                            ));
                    } else if ($attributeValue == $otherEntityAttrValue) {
                        $estaDuplicado = $this->pertenceAoCompartilhamento($sei);
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
                    $this->errorEntidadeDuplicada();

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

            if ($attributeValue instanceof AbstractEntityWithId) {
                if (!$this->em->getRepository(\App\Entity\AbstractEntity::getClass($attributeValue))->find(
                    $attributeValue->getId()
                )
                ) {
                    // Está verificando a duplicidade de valores em uma coluna FK (que aponta para outra tabela),
                    // porém o registro da outra tabela ainda não existe, então com certesa esta coluna não está duplicada.
                    // O regsitro da outra tabela pode não existir quando ainda não foi invocado EntityManager::flush().
                    return true;
                }
            }

            $qb->andWhere("a." . $attributeName . " = :attributeValue" . (++$count));
            $qb->setParameter('attributeValue' . $count, $attributeValue);
        }


        $notInIds = [];
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

        $ownerCompanyIds = $this->idDasEmpresasCompartilhando;
        if ($ownerCompanyIds) {
            $ownerCompanyAttr = $this->annoWithCompany->attrName;
            if (count($ownerCompanyIds) == 1) {
                $qb->andWhere($qb->expr()->eq("a.$ownerCompanyAttr", ':ownerCompany'));
                $ownerCompanyIds = reset($ownerCompanyIds);
            } else {
                $qb->andWhere($qb->expr()->in("a.$ownerCompanyAttr", ':ownerCompany'));
            }
            $qb->setParameter('ownerCompany', $ownerCompanyIds);
        }

        $qb->setMaxResults(1);

        if ($qb->getQuery()->getOneOrNullResult() !== null) {
            $this->errorEntidadeDuplicada();

            return false;
        }

        return true;
    }

    private function pertenceAoCompartilhamento(AbstractEntity $otherEntity)
    {
        if (!$this->annoWithCompany || $this->sharingForbidden) {
            // se esta company não permite compartilhamentos, nos retornamos true, pq qualquer registro é acessado
            // entre todas as empresas existentes.
            return true;
        }

        if (count($this->idDasEmpresasCompartilhando)) {
            /** @var $emp Company */
            $emp = $otherEntity->get($this->annoWithCompany->attrName);
            if (in_array($emp->getId(), $this->idDasEmpresasCompartilhando)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    private function errorEntidadeDuplicada()
    {
        if ($this->annoWithCompany && !$this->sharingForbidden) {
            $this->error(self::DUPLICATE_ENTITY_WITH_COMPANIES);
        } else {
            $this->error(self::DUPLICATED_ENTITY);
        }
    }

}
