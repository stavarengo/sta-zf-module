<?php
namespace Sta\Entity;

/**
 * Interface EntityWithIdInterface
 *
 * @package Sta\Entity
 */
interface EntityWithIdInterface extends EntityInterface
{

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     *
     * @return $this
     */
    public function setId($id);

}
