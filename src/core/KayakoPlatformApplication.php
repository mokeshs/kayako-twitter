<?php
/**
 * This file is part of the Kayako-twitter package.
 *
 * Copyright (c) 2015 Mukesh Sharma <cogentmukesh@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kayako;

use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\TwigServiceProvider;

/**
 * Kayako Platform Request Class
 *   - Create Silex Application
 *   - Register Application Controllers
 *   - Handle Request
 *
 * @author Mukesh Sharma <cogentmukesh@gmail.com>
 * @since  Thu Apr  2 09:45:24 IST 2015
 * @see http://silex.sensiolabs.org/doc/usage.html
 *
 * @package Kayako
 */
class KayakoPlatformApplication extends \Silex\Application
{
    /**
     * Class Constructor
     *
     * Instantiate a new Application.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values The parameters or objects.
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);

        // Configure Debug Mode
        $this['debug'] = $this['config']['general']['debug']; 
        
        // Register Services  
        $this->registerServices();
    }
    
    /**
     * Handles the request and delivers the response.
     *
     * @param Request|null $request Request to process
     */
    public function run(Request $request = null)
    {
        // Register Application Controllers
        $this->registerControllers();
        parent::run($request);
    }
    
    /**
     * Registers service providers.
     * TODO: Get it from Services Configuraiton like services.yml or services.php
     *
     * @return KayakoPlatformApplication 
     */
    protected function registerServices()
    {
        // Register Twig Service
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/../views',
        ));
        // Add more services here

        // give it some fluent interface
        return $this;
    }

    /**
     * Register Application Controllers
     * TODO: Read it from Routing Configuration routing.yml
     *
     * @return KayakoPlatformApplication 
     */
    protected function registerControllers()
    {
        // Default Controller
        // Maps a GET request to a callable.
        $this->get('/',      'Kayako\\Twitter\\TwitterController::indexAction'); 
        $this->get('/fetch', 'Kayako\\Twitter\\TwitterController::fetchAction'); 
        // Add more controllers here
        
        return $this;
    }
}
