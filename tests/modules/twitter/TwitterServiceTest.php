<?php

use Kayako\Twitter\TwitterService;
use Kayako\Twitter\TwitterCacheRedis;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class TwitterServiceTest
 *
 * @author Mukesh Sharma <mukesh.sharma@rightster.com>
 * @since  Tue Apr 14 08:13:34 IST 2015
 */
class TwitterServiceTest extends KayakoPlatformTest
{
    /**
     * @var TwitterService
     */
    private $mod = null;
    
    /**
     * @var TwitterOAuth 
     */
    private $twitterAuthMocked = null;
    
    /**
     * @var TwitterCacheRedis
     */
    private $twitterCacheMocked = null;

    /**
     * @var array
     */
    private $tweetsFixtureData = array(
        array(
            'id_str'        => '123',
            'text'          => 'my tweet',
            'screen_name'   => 'test',
            'name'          => 'test',
            'retweet_count' => 3
        ),
        array(
            'id_str'        => '123223',
            'text'          => 'my tweet2',
            'screen_name'   => 'test2',
            'name'          => 'test2',
            'retweet_count' => 3
        )
    );

    public function setUp()
    {
        parent::setUp();

        $this->twitterAuthMocked  = $this->getMockedClass('Abraham\TwitterOAuth\TwitterOAuth');
        $this->twitterCacheMocked = $this->getMockedClass('Kayako\Twitter\TwitterCacheRedis');
        $this->mod = new TwitterService($this->twitterAuthMocked, $this->twitterCacheMocked);
    }

    /**
     * processMaxId Tests
     */

    /**
     * @test
     * @dataProvider processMaxIdDataProvider
     */
    public function processMaxId($input, $expectation)
    {
        $method = $this->setMethodAccessible(get_class($this->mod), 'processMaxId');
        $this->assertEquals(
            $method->invokeArgs($this->mod, array($input)),
            ((PHP_INT_SIZE === 8) ? $expectation : $input)
        );
    }

    /**
     * validateSearchParams Tests
     */
    
    /**
     * @test
     * @dataProvider invalidSearchParamDataProvider
     *
     * @expectedException InvalidArgumentException
     */
    public function validateSearchParamsThrowsInvalidArgumentException($searchParams)
    {
        $method = $this->setMethodAccessible(get_class($this->mod), 'validateSearchParams');
        $method->invokeArgs($this->mod, array($searchParams));
    }
    
    /**
     * @test
     */
    public function validateSearchParamsSucceeds()
    {
        $method = $this->setMethodAccessible(get_class($this->mod), 'validateSearchParams');
        $this->assertTrue(
            $method->invokeArgs($this->mod, array(array('query' => '#custserv', 'maxId' => 10, 'fetchCount' => 50)))
        );
    }

    /**
     * getTweets Tests
     */
    
    /**
     * @test
     */
    public function getTweetsFromCacheSucceeds()
    {
        $searchParams = array(
            'query'       => '#test',
            'fetchCount'  => 100,
            'maxId'       => 1232435346,
            'filters'     => array(
                'minReTweet'  => 1
            )  
        );
        $limit  = 2;

        // Set Twitter Cache `getTweets` expectation
        $this->twitterCacheMocked->expects($this->once())
            ->method('getTweets')
            ->with($searchParams['query'], $searchParams['maxId'], $limit)
            ->will($this->returnValue($this->tweetsFixtureData));

        // Get Twitter Service Mock
        $mod = $this->makePartialMockedClass(
            get_class($this->mod), 
            array('processMaxId', 'getCacheNamespace'), 
            array($this->twitterAuthMocked, $this->twitterCacheMocked)
        );

        $mod->expects($this->once())
            ->method('processMaxId')
            ->with($searchParams['maxId'])
            ->will($this->returnValue($searchParams['maxId']));
        
        $mod->expects($this->once())
            ->method('getCacheNamespace')
            ->with($searchParams['query'])
            ->will($this->returnValue($searchParams['query']));

        $this->assertEquals(
            $mod->getTweets($searchParams, $limit),
            array(
                'status' => true,
                'tweets' => $this->tweetsFixtureData,
            )
        );
    }
    

    /**
     * Data Providers
     */

    /**
     * Process MaxId Data Provider
     */
    public function processMaxIdDataProvider()
    {
        return array(
            array(5, 4),
            array(null, null),
            array(0, 0),
        );
    }
    
    /**
     * Invalid Search Param DataProvider
     */
    public function invalidSearchParamDataProvider()
    {
        return array(
            array(array()),
            array(array('query' => 'abc', 'fetchCount' => 3)),
            array(array('query' => 'abc', 'maxId' => 2)),
            array(array('query' => '    ', 'maxId' => 2)),
            array(array('query' => null, 'maxId' => 2)),
            array(array('query' => '#adsf', 'maxId' => 2, 'fetchCount' => 2000000)),
        );
    }
    
    public function tearDown()
    {
        $this->mod = null;
    }
}
