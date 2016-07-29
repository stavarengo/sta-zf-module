<?php
/**
 * clap Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\Entity\Id;

use Doctrine\ORM\EntityManager;

class RandomIdGenerator extends \Doctrine\ORM\Id\AbstractIdGenerator
{

    /**
     * Generates an identifier for an entity.
     *
     * @param EntityManager|EntityManager $em
     * @param \Doctrine\ORM\Mapping\Entity $entity
     *
     * @return mixed
     */
    public function generate(EntityManager $em, $entity)
    {
        return self::generateNewId();
    }

    public static function generateNewId()
    {
        return uniqid();
    }
}
