<?php
/**
 * This file is part of the Kayako-twitter package.
 *
 * Copyright (c) 2015 Mukesh Sharma <cogentmukesh@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kayako\Twitter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Silex\Provider\TwigServiceProvider;
use Kayako\KayakoPlatformApplication as Application;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Application Tweeter Controller
 *
 * @author Mukesh Sharma <cogentmukesh@gmail.com>
 * @since  Mon Apr  6 09:45:24 IST 2015
 *
 * @package Kayako\Twitter
 */
class TwitterController
{
    /**
     * Index Action
     *
     * @param Request $request Request Container
     * @param Application $app KayakoPlatformApplication Instance
     *
     * @return string  
     */
    public function indexAction(Request $request, Application $app)
    {
        return $app['twig']->render('twitter/index.html.twig');
    } 
    
    /**
     * Fetch Action
     *
     * @param Request $request Request Container
     * @param Application $app KayakoPlatformApplication Instance
     *
     * @return JsonResponse
     */
    public function fetchAction(Request $request, Application $app)
    {
        // Get the request param `maxId` or set default as null
        $maxId        = $request->get('maxId', null);
        $config       = $app['config']['twitter'];

        // Create Search Parameters
        //
        // This gives us felxibility of accepting the same from UI via POST as well
        // so that we can create a full-fledge twitter search client. e.g. replace query with #ipl
        $searchParams = array(
            'query'       => '#custserv',
            'fetchCount'  => 100,
            'maxId'       => $maxId,
            'filters'     => array(
                'minReTweet'  => 1
            )  
        );

        // Get the Twitter Service Handle
        $twitter = new TwitterService(
            new TwitterOAuth(
                $config['customerKey'],
                $config['customerSecret'],
                $config['accessToken'],
                $config['accessTokenSecret']
            ), 
            new TwitterCacheRedis(new \Predis\Client())
        );

        // Return tweets in json format on success
        return new JsonResponse($twitter->getTweets($searchParams));
    }
}
