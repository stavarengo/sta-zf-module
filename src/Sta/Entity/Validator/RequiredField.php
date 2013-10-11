<?php
namespace Sta\Entity\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;
use Zend\Validator\NotEmpty;

/**
 * @author: Stavarengo
 */
class RequiredField extends AbstractValidator
{

	const REQUIRED_FIELD = 'requiredField';
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
		self::REQUIRED_FIELD => 'O campo "%field%" é necessário e não pode ser vazio.',
	);
	/**
	 * @var array
	 */
	protected $messageVariables = array(
		'field' => 'field',
	);
	/**
	 * @var string
	 */
	protected $field;

	public function isValid($value)
	{
		if (!$value instanceof RequiredFieldValue) {
			throw new \Sta\Entity\Validator\InvalidArgument();
		}

		$entity   = $value->entity;
		$notEmpty = new NotEmpty();
		foreach ($value->attributes as $attributeName) {
			$attributeValue = $entity->get($attributeName);
			if (!$notEmpty->isValid($attributeValue)) {
				$this->field = $attributeName;
				$this->error(self::REQUIRED_FIELD);
				return false;
			}
		}

		return true;
	}
}