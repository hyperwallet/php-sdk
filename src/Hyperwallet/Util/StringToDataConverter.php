<?php


namespace Hyperwallet\Util;


class StringToDataConverter
{
    static function convertStringToDate($string, $format)
    {
        $stringToDate = $string === null ? null : new \DateTime($string);
        return $stringToDate == null ? null : $stringToDate->format($format);
    }
}