<?php


namespace Hyperwallet\Tests\Util;


use Hyperwallet\Util\StringToDataConverter;

class StringToDateConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldConvertStringToDateIfAcceptedParameterIsNotNull()
    {
        $format = 'Y-m-d';
        $stringToConvert = '2019-12-31';

        $convertedDate = StringToDataConverter::convertStringToDate($stringToConvert, $format);
        $originalDate = new \DateTime($stringToConvert);

        $this->assertEquals($originalDate->format($format), $convertedDate);
    }

    public function testShouldReturnNullIfAcceptedParameterIsNull()
    {
        $format = 'Y-m-d';
        $stringToConvert = null;

        $convertedDate = StringToDataConverter::convertStringToDate($stringToConvert, $format);
        $originalDate = null;

        $this->assertEquals(null, $convertedDate);
    }
}