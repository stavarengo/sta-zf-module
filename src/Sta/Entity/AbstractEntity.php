<?php
namespace Sta\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Sta\Entity\Exception\InvalidArgument;

/**
 * @author: Stavarengo
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntity
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", nullable=true)
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @var int
	 */
	protected $id;

	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return \Sta\Module::getServiceLocator()->get('Doctrine\ORM\EntityManager');
	}

	/**
	 * Helper que retornar o valor de um atributo sem precisar usar os getters.
	 *
	 * @param $attributeName
	 *
	 * @throws \Sta\Entity\Exception\InvalidArgument
	 * @return mixed
	 */
	public function get($attributeName)
	{
		$method = 'get' . ucfirst($attributeName);
		if (is_callable(array($this, $method))) {
			return $this->$method();
		}
		$method = 'is' . ucfirst($attributeName);
		if (is_callable(array($this, $method))) {
			return $this->$method();
		}

		throw new InvalidArgument('Não existe um método para retornar o valor do atributo: "'
			. $attributeName . '"');
	}

	/**
	 * @param string $attributeName
	 * @param $value
	 *
	 * @throws \Sta\Entity\Exception\InvalidArgument
	 */
	public function set($attributeName, $value)
	{
		$method = 'set' . ucfirst($attributeName);
		if (is_callable(array($this, $method))) {
			$this->$method($value);
			return;
		}

		throw new InvalidArgument('Não existe um método para definir o valor do atributo: "'
			. $attributeName . '"');
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

}