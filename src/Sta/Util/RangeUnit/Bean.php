<?php
namespace Sta\Util\RangeUnit;

/**
 * @author Stavarengo
 */
class Bean
{

	/**
	 * @var int
	 */
	protected $start;

	/**
	 * @var int
	 */
	protected $end;

	/**
	 * @var string
	 */
	protected $unit;

	/**
	 * @param $start
	 * @param $end
	 * @param $unit
	 */
	public function __construct($start, $end, $unit)
	{
		$this->start = (int)$start;
		$this->end   = (int)$end;
		$this->unit  = (string)$unit;
	}

	public function __toString()
	{
		return "$this->unit=$this->start-$this->end";
	}

	/**
	 * @param int $end
	 */
	public function setEnd($end)
	{
		$this->end = $end;
	}

	/**
	 * @param int $start
	 */
	public function setStart($start)
	{
		$this->start = $start;
	}

	/**
	 * @return int
	 */
	public function getEnd()
	{
		return $this->end;
	}

	/**
	 * @return int
	 */
	public function getLength()
	{
		return ($this->getEnd() - $this->getStart() + 1);
	}

	/**
	 * @return int
	 */
	public function getStart()
	{
		return $this->start;
	}

	/**
	 * @param string $unit
	 */
	public function setUnit($unit)
	{
		$this->unit = $unit;
	}

	/**
	 * @return string
	 */
	public function getUnit()
	{
		return $this->unit;
	}

}