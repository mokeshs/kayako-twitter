<?php

//require_once "KayakoPlatfromUtilsTrait.php";

/**
 * KayakoPlatformTest Class
 * Base class for the tests, Bootstrap test suit and provides 
 * utility methods
 */
class KayakoPlatformTest extends PHPUnit_Framework_TestCase
{
    /**
     * Use Reflection to set the Protected and Private methods accessible 
     * in order to test them
     *
     * @param string $className
     * @param string $method
     */
    protected function setMethodAccessible($className, $method)
    {
        $method = new ReflectionMethod($className, $method);
        $method->setAccessible(TRUE);

        return $method;
    }
    
    /**
     * Creates a new Mocked Class.
     *
     * @param string $className
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedClass($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a partial mock
     *
     * @param string $className Class to be Mocked
     * @param array  $functions Array of Function names to mocked, rest of the class function 
     *                          will not be mocked and preserve there original behaviour
     * @param array  $args Constructor arguments 
     *
     * @return object The mocked object
     */
    protected function makePartialMockedClass($className, $functions, array $args)
    {
        return $this->getMockBuilder($className)
            ->setMethods($functions)
            ->setConstructorArgs($args)
            ->getMock();
    }

    public static function bootstrapLoader()
    {
        require_once(__DIR__ . '/../../vendor/autoload.php');
    }
}

KayakoPlatformTest::bootstrapLoader();
