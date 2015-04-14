<?php

use Kayako\Twitter\TwitterController;

/**
 * Class TwitterControllerTest
 *
 * @author Mukesh Sharma <mukesh.sharma@rightster.com>
 * @since  Mon Apr 13 23:45:08 IST 2015
 */
class TwitterControllerTest extends KayakoPlatformTest
{
    /**
     * @var TwitterController
     */
    private $mod = null;

    public function setUp()
    {
        parent::setUp();
        $this->mod = new TwitterController();
    }

    /**
     * @test
     */
    public function indexActionSucceeds()
    {
        $template         = 'twitter/index.html.twig';
        $expectation      = 'Kayako Twitter Client';
        
        // Create Mock of Request Class
        $requestMock      = $this->getMockedClass('Symfony\Component\HttpFoundation\Request');
        // Create Application
        $application      = new Kayako\KayakoPlatformApplication(array('config' => null));
        // Create Twig Provider Mock
        $twigProviderMock = $this->getMockBuilder('stdClass')
            ->setMethods(array('render'))
            ->getMock();

        // Set Twig `render` expectations
        $twigProviderMock->expects($this->once())
            ->method('render')
            ->with($template)
            ->will($this->returnValue($expectation));
        // Inject twig service in to the Application
        $application['twig'] = $twigProviderMock;

        $this->assertEquals(
            $this->mod->indexAction($requestMock, $application),
            $expectation
        );
    }
    
    /**
     * @test
     */
    public function fetchActionSucceeds()
    {
        // Test Fixture
        $maxId       = 12345;
        $expectation = '{"status":true,"tweets":[]}';
        
        // Create Mock of Request Class
        $requestMock = $this->getMockedClass('Symfony\Component\HttpFoundation\Request');
        // Set Request `get` Expectations
        $requestMock->expects($this->once())
            ->method('get')
            ->with('maxId', null)
            ->will($this->returnValue($maxId));

        // Create Application
        $application = new Kayako\KayakoPlatformApplication(array('config' => null));

        // Inject config properties in to the Application
        $application['config'] = array(
            'twitter' => array(
                'customerKey'       => 'myCustomerKey',
                'customerSecret'    => 'mYSuPeRSeCrEt',
                'accessToken'       => 'testt0ken',
                'accessTokenSecret' => 't0kenSeCrET',
            )
        );

        // Call FetchAction
        $response = $this->mod->fetchAction($requestMock, $application);
        
        // Assert response
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
    }
    
    public function tearDown()
    {
        $this->mod = null;
    }
}
