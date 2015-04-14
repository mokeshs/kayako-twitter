<?php

use Kayako\Util;

/**
 * Util Test Class
 */
class UtilTest extends KayakoPlatformTest
{
    /**
     * @test
     * @dataProvider invalidStringDataProvider
     */
    public function isValidStringReturnsFalse($string)
    {
        $this->assertFalse(Util::isValidString($string));
    }
    
    /**
     * @test
     * @dataProvider validStringsDataProvider
     */
    public function isValidStringReturnsTrue($string)
    {
        $this->assertTrue(Util::isValidString($string));
    }
    
    /**
     * @test
     */
    public function isValidArrayOfStrings()
    {
        $this->assertTrue(Util::isValidArrayOfStrings(array('simple', 'test', '!!!')));

        foreach ($this->invalidStringDataProvider() as $item) {
            $this->assertFalse(Util::isValidArrayOfStrings($item));
        }
 
        $this->assertFalse(Util::isValidArrayOfStrings(array()));
    }

    /**
     * Data Providers
     */
    
    /**
     * Provides valid strings
     */
    public function validStringsDataProvider()
    {
        return array(
            array('Test'),
            array('Foo-Bar'),
            array('a'),
            array('12345'),
            array('TRUE')
        );
    }
    
    /**
     * Provides invalid strings
     */
    public function invalidStringDataProvider()
    {
        return array(
            array('     '),
            array(null),
            array(false),
            array("\n"),
        );
    }
}

