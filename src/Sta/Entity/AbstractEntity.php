<?php
/**
 * Sell Project (http://sell.grandsinformatica.com.br/)
 *
 * @link      https://github.com/grandssistemas/sell Código fonte
 */

namespace Sta\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Sta\Entity\Exception\InvalidArgument;

/**
 * Classe base para todas as entidades do Sell.
 * 
 * Uma entidade é um conjunto de informações que podem ser idenficado por um ID.
 * 
 * @author: Stavarengo
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntity
{

	/**
     * Identificação da entidade.
     * O valor deste atributo é controlado pelo WebService.
     * Você deve armazenar este ID em sua base de dados para que você possa fazer uma ligação entre os registros da
     * sua base de dados com as entidades do WebService.
	 * @ORM\Id
	 * @ORM\Column(type="integer", nullable=true)
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @var int
	 */
	protected $id;

	/**
     * @ignore
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
	 * @ignore
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
     * @ignore
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
     * @ignore
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

}