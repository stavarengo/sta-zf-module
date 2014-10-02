<?php
namespace Sta\Util;

use Sta\Exception;

class StringFormats
{

    /**
     * Formata um número de telefone.
     * Créditos:
     *    Função original - http://www.danielkassner.com/2010/05/21/format-us-phone-number-using-php
     *    Adaptada por Rafael Stavarengo
     *
     * @param string $phone
     *        O telefone que será formatado.
     * @param boolean $convert
     *        Default true. Determina se as letras serão corespondidas em seus respectivos números.
     *        Exemplo:
     *            1-800-TERMINIX, vira (180) 0837-6464
     *            1-800-Flowers, vira (180) 0356-9377
     *            18-3666-Sony, vira (18) 3666-7669
     *
     * @return string
     */
    public static function formatPhone($phone, $convert = true)
    {
        // If we have not entered a phone number just return empty
        if (empty($phone)) {
            return '';
        }

        // Strip out any extra characters that we do not need only keep letters and numbers
        $phone = preg_replace('/[^0-9A-Za-z]/', '', $phone);
        // Keep original phone in case of problems later on but without special characters
        $OriginalPhone = $phone;

        // Do we want to convert phone numbers with letters to their number equivalent?
        // Samples are: 1-800-TERMINIX, 1-800-FLOWERS, 1-800-Petmeds
        if ($convert == true && !is_numeric($phone)) {
            $replace = array(
                '2' => array('a', 'b', 'c'),
                '3' => array('d', 'e', 'f'),
                '4' => array('g', 'h', 'i'),
                '5' => array('j', 'k', 'l'),
                '6' => array('m', 'n', 'o'),
                '7' => array('p', 'q', 'r', 's'),
                '8' => array('t', 'u', 'v'),
                '9' => array('w', 'x', 'y', 'z')
            );

            // Replace each letter with a number
            // Notice this is case insensitive with the str_ireplace instead of str_replace
            foreach ($replace as $digit => $letters) {
                $phone = str_ireplace($letters, $digit, $phone);
            }
        }

        $length = strlen($phone);
        // Perform phone number formatting here
        switch ($length) {
            case 7:
            case 8:
                // Format: xxx-xxxx ou xxxx-xxxx
                return preg_replace('/([0-9a-zA-Z]{3,4})([0-9a-zA-Z]{4})/', '$1-$2', $phone);
            case 10:
                // Format: (xx) xxxx-xxxx
                return preg_replace('/([0-9a-zA-Z]{2})([0-9a-zA-Z]{4})([0-9a-zA-Z]{4})/', '($1) $2-$3', $phone);
            case 11:
                // Format: (xxx) xxxx-xxxx
                return preg_replace('/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})([0-9a-zA-Z]{4})/', '($1) $2-$3', $phone);
            default:
                // Return original phone if not 7, 10 or 11 digits long
                return $OriginalPhone;
        }
    }

    public static function formatarCep($cep)
    {
        $cep = trim($cep);
        if (!$cep) return $cep;

        // Remove todos os carecteres, exceto os números
        $cep = preg_replace('/[^0-9]/', '', $cep);

        $length = strlen($cep);
        if ($length == 8) {
            return preg_replace('/([0-9]{5})([0-9]{3})/', '$1-$2', $cep);
        } else {
            return $cep;
        }
    }

    public static function formatarCpfCnpj($cpfCnpj)
    {
        $length = strlen($cpfCnpj);
        if ($length == 14) {
            return self::formatarCnpj($cpfCnpj);
        }
        return self::formatarCpf($cpfCnpj);
    }

    public static function formatarCnpj($cnpj)
    {
        $cnpj = trim($cnpj);
        if (!$cnpj) return $cnpj;

        // Remove todos os carecteres, exceto os números
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        $length = strlen($cnpj);
        if ($length == 14) {
            return preg_replace('/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{2})/', '$1.$2.$3/$4-$5', $cnpj);
        } else {
            return $cnpj;
        }
    }

    public static function formatarCpf($cpf)
    {
        $cpf = trim($cpf);
        if (!$cpf) return $cpf;

        // Remove todos os carecteres, exceto os números
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        $length = strlen($cpf);
        if ($length == 11) {
            return preg_replace('/([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{2})/', '$1.$2.$3-$4', $cpf);
        } else {
            return $cpf;
        }
    }

    public static function printSQL($sql, $return = false, $html = false, $depth = 0)
    {
        if ($sql instanceof \Doctrine\ORM\QueryBuilder) {
            $params = $sql->getParameters();
            $sql    = $sql->getQuery()->getSQL();
            /** @var $param \Doctrine\ORM\Query\Parameter */
            foreach ($params as $param) {
                $paramValue    = $param->getValue();
                $sqlParamValue = $paramValue;
//                $paramName     = $param->getName();

                if ($paramValue instanceof \DateTime) {
                    $sqlParamValue = "'" . $paramValue->format('Y-m-d H:m:s') . "'";
                } else if ($paramValue instanceof \Doctrine\ORM\QueryBuilder) {
                    $sqlParamValue = "\n" . self::printSQL($paramValue, true, $html, $depth + 1);
                } else if ($paramValue instanceof \Sta\Entity\AbstractEntity) {
                    $sqlParamValue = $paramValue->getId();
                } else if (is_array($paramValue)) {
                    $valueAux = array();
                    foreach ($paramValue as $value) {
                        if ($value instanceof \Sta\Entity\AbstractEntity) {
                            $valueAux[] = $value->getId();
                        }
                    }
                    $sqlParamValue = implode(',', $valueAux);
                }

                $sql = preg_replace('/\?/', $sqlParamValue, $sql, 1);
            }
        }

        $depth = str_repeat("\t", $depth);

        $sql = str_replace('SELECT ', "{$depth}\bSELECT\b\n{$depth}\t", $sql);
        $sql = str_replace(', ', ",\n{$depth}\t", $sql);
        $sql = str_replace('`, ', "`,\n{$depth}\t", $sql);
        $sql = str_replace(' AS ', " \bAS\b ", $sql);
        $sql = str_replace('CASE', "\bCASE\b", $sql);
        $sql = str_replace(' LIKE ', " \bLIKE\b ", $sql);
        $sql = str_replace(' WHEN ', "\n{$depth}\t{$depth}\t\bWHEN\b ", $sql);
        $sql = str_replace(' ELSE ', "\n{$depth}\t{$depth}\t\bELSE\b ", $sql);
        $sql = str_replace(' END', "\n{$depth}\t\bEND\b", $sql);
        $sql = str_replace(' THEN ', " \bTHEN\b ", $sql);
        $sql = preg_replace('/COUNT\((.+?)\)/', "\bCOUNT(\b$1\b)\b", $sql);
        $sql = str_replace(' FROM ', "\n{$depth}\bFROM\b ", $sql);
        $sql = str_replace(' INNER JOIN ', "\n{$depth}\bINNER JOIN\b ", $sql);
        $sql = str_replace(' LEFT JOIN ', "\n{$depth}\bLEFT JOIN\b ", $sql);
        $sql = str_replace(' ON ', " \bON\b ", $sql);
        $sql = str_replace(' OR ', " \bOR\b ", $sql);
        $sql = str_replace(') AND (', ")\n{$depth}\bAND\b (", $sql);
        $sql = str_replace(' AND ', "\n{$depth}\bAND\b ", $sql);
        $sql = str_replace(' WHERE ', "\n{$depth}\bWHERE\b ", $sql);
        $sql = str_replace(' GROUP BY', "\n{$depth}\bGROUP BY\b", $sql);
        $sql = str_replace(' ORDER BY', "\n{$depth}\bORDER BY\b", $sql);
        $sql = str_replace(' WHERE ', "\n{$depth}\bWHERE\b ", $sql);
        $sql = str_replace(' LIMIT ', "\n{$depth}\bLIMIT\b ", $sql);
        $sql = str_replace(' OFFSET ', " \bOFFSET\b ", $sql);
        $sql = str_replace(' DESC', " \bDESC\b", $sql);
        $sql = str_replace(' IS ', " \bIS\b ", $sql);
        $sql = str_replace(' NOT ', " \bNOT\b ", $sql);
        $sql = str_replace(' NULL', " \bNULL\b", $sql);
        $sql = preg_replace('/(SELECT.*\t)\((.+)\)(.*FROM)/s', "$1(\n\t\t$2\n\t)$3", $sql);

        if ($html) {
            $sql = preg_replace(array(
                "/\n/",
                "/\t/",
                "/\\\b(.+?)\\\b/",
            ), array(
                '<br>',
                '&nbsp;&nbsp;&nbsp;&nbsp;',
                '<b>$1</b>',
            ), $sql);
            $sql = '<span style="font-family: Monaco, Menlo, Consolas, Courier New, monospace">' . $sql . '</span>';
        } else {
            $sql = preg_replace(array(
                "/\\\b/",
            ), array(
                '',
            ), $sql);
        }

        if ($return) {
            return $sql;
        } else {
            echo $sql;
        }
    }

    /**
     * Substitui os caracteres invalidos de uma URL.
     * @param string $url
     *        Está não é a URI completa. Esta deve ser apenas a string que você deseja corrigir antes de acrescentar em uma
     *        url de verdade.
     * @param string $wordSeparator
     * @param null $maxLength
     * @throws \Sta\Exception
     * @return string
     */
    public static function normalizeUrl($url, $wordSeparator = '', $maxLength = null)
    {
        $wordSeparatorAllowed = array(
            '.', '-', '_'
        );
        if (!in_array($wordSeparator, $wordSeparatorAllowed)) {
            throw new Exception("The word separator '$wordSeparator', is not allowed.");
        }

        $url = trim(mb_strtolower($url, 'UTF-8'));
        if ($maxLength !== null) {
            $url = mb_substr($url, 0, $maxLength, 'UTF-8');
        }

        $charsToReplace = array(
            '  '                            => ' ',
            ' '                             => $wordSeparator,
            $wordSeparator . $wordSeparator => $wordSeparator,
            '--'                            => '-',
            '__'                            => '_',
            '_-_'                           => '-',
            '-_-'                           => '-',
            '_-'                            => '-',
            '-_'                            => '-',
        );

        foreach ($charsToReplace as $search => $replace) {
            do {
                $url = str_ireplace($search, $replace, $url);
            } while (stripos($url, $search) !== false);
        }

        $url = self::removeAccents($url);

        $allWordsSeparatorsTogether = implode('', $wordSeparatorAllowed);
        
        $regex = '[^a-z0-9' . $allWordsSeparatorsTogether . ']'; //remove os caractres não alfa numericos e não permitidos na url
        $regex = preg_quote($regex, '/');
        $url   = preg_replace('/' . $regex . '/i', '', $url);

        $url = trim($url, ' ' . $allWordsSeparatorsTogether);

        if ($maxLength) {
            while(mb_strlen($rawUrl = rawurldecode($url), 'UTF-8') > $maxLength) {
                $url = mb_substr($url, 0, mb_strlen($url) - 1);
            };
        } else {
            $rawUrl = rawurldecode($url);
        }
        return $rawUrl;
    }

    /**
     * @param $string
     * @return string
     */
    public static function removeAccents($string)
    {
        $array1 = array(
            'á', 'à', 'â', 'ã', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û',
            'ü', 'ç', 'Á', 'À', 'Â', 'Ã', 'Ä', 'É', 'È', 'Ê', 'Ë', 'Í', 'Ì', 'Î', 'Ï', 'Ó', 'Ò', 'Ô', 'Õ', 'Ö', 'Ú',
            'Ù', 'Û', 'Ü', 'Ç'
        );
        $array2 = array(
            'a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u',
            'u', 'c', 'A', 'A', 'A', 'A', 'A', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U',
            'U', 'U', 'U', 'C'
        );
        $string  = str_replace($array1, $array2, $string);
        
        //remove os ascentos
        $string = iconv('UTF-8', 'US-ASCII//TRANSLIT', $string);
        
        return $string;
    }
}