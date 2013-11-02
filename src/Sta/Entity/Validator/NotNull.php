<?php
namespace Sta\Entity\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;
use Zend\Validator\NotEmpty;

/**
 * @author: Stavarengo
 */
class NotNull extends AbstractValidator
{

	const NOT_NULL_FIELD = 'not_null_field';
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
		self::NOT_NULL_FIELD => 'O atributo "%entityName%::%field%" é necessário e não pode ser vazio.',
	);
	/**
	 * @var array
	 */
	protected $messageVariables = array(
		'field'      => 'field',
		'entityName' => 'entityName',
	);
	/**
	 * @var string
	 */
	protected $field;
	/**
	 * @var string
	 */
	public $entityName;

	public function isValid($value)
	{
		if (!$value instanceof NotNullValue) {
			throw new \Sta\Entity\Validator\InvalidArgument();
		}

		$entity   = $value->entity;
		foreach ($value->attributes as $attributeName) {
			$attributeValue = $entity->get($attributeName);
			if ($attributeValue === null) {
				$this->entityName = get_class($entity);
				$this->field      = $attributeName;
				$this->error(self::NOT_NULL_FIELD);
				return false;
			}
		}

		return true;
	}
}