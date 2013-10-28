<?php
namespace Sta;

abstract class Enum
{

	/**
	 * @var int
	 */
	private $value;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private static $alreadyInitiated = array();

	/**
	 * @param int|array $value
	 * @param string $name
	 */
	private function __construct($value, $name = null)
	{
		if (is_array($value)) {
			if (count($value) == 2) {
				$name = $value[1];
			}
			$value = $value[0];
		}

		$this->value = $value;
		$this->name  = ($name ? $name : $value);
	}

	/**
	 * @param string $class
	 */
	public static function start($class)
	{
		if (array_key_exists($class, self::$alreadyInitiated)) return;

		$reflect    = new \ReflectionClass($class);
		$properties = $reflect->getStaticProperties();

		self::$alreadyInitiated[$class] = array(
			'reflect'    => $reflect,
			'properties' => $properties,
		);

		foreach ($properties as $propName => $propValue) {
			$newValue = new $class($propValue);
			$reflect->setStaticPropertyValue($propName, $newValue);
		}
	}

//	public static function get($value)
//	{
//		$properties = self::$alreadyInitiated[get_class(self)]['properties'];
//
//		foreach ($properties as $propName => $propValue) {
//			if (is_array($propValue)) {
//				$newValue = new $class($propValue[0], $propValue[1]);
//			} else {
//				$newValue = new $class($propValue);
//			}
//			$reflect->setStaticPropertyValue($propName, $newValue);
//		}
//	}

	/**
	 * @return int
	 */
	public function getValue()
	{
		return (int)$this->value;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->name;
	}

	/**
	 * @param Enum $other
	 *
	 * @return bool
	 */
	public function equals(Enum $other)
	{
		if ($other === null || !is_object($other)) {
			return false;
		}
		if (get_class($this) != get_class($other)) {
			return false;
		}

		return ($this->getValue() == $other->getValue());
	}
}