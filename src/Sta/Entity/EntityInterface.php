<?php
/**
 * clap Project ${PROJECT_URL}
 *
 * @link      ${GITHUB_URL} Source code
 */

namespace Sta\Entity;

interface EntityInterface
{
    /**
     * Helper que retornar o valor de um atributo sem precisar usar os getters.
     *
     * @param $attributeName
     *
     * @ignore
     * @throws \Sta\Entity\Exception\InvalidArgument
     * @return mixed
     */
    public function get($attributeName);

    /**
     *
     * @param string $attributeName
     * @param $value
     *
     * @throws \Sta\Entity\Exception\InvalidArgument
     *
     * @return $this
     */
    public function set($attributeName, $value);

}
