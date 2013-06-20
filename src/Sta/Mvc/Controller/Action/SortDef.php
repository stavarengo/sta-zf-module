<?php
namespace Sta\Mvc\Controller\Action;

use Sta\Enum;

/**
 * @author Stavarengo
 */
class SortDef extends Enum
{
	/**
	 * @var SortDef
	 */
    public static $DESC = array(1, 'DESC');
	/**
	 * @var SortDef
	 */
    public static $ASC = array(2, 'ASC');
}

Enum::start(__NAMESPACE__ . '\SortDef');