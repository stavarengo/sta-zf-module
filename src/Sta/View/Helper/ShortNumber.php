<?php
namespace Sta\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ShortNumber extends AbstractHelper
{

    /**
     * Formata números agrupando por milhares.
     * Ex: 1k, 250m, etc
     * 
     * @param $number
     *
     * @return string
     */
    public function __invoke($number)
    {
        $thousandsSep = ',';
        $numberStr    = number_format($number, 0, '.', $thousandsSep);
        $numberParts  = explode($thousandsSep, $numberStr);
        $units        = array(
            '',
            'K',// Thousand
            'M',// Million
            'B',// Billion
            'T',// Trillion
            'Qa',// Quadrillion
            'Qi',// Quintillion
        );

        $howManyParts = count($numberParts);

        if ($howManyParts > count($units)) {
            throw new \Sta\Exception\InvalidArgumentException('This number is bigger than the maximum we support.');
        }

        return $numberParts[0] . $units[$howManyParts - 1];
    }

}