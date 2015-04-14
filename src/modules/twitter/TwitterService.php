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

/**
 * Twitter Service Class
 * 
 * @author Mukesh Sharma <cogentmukesh@gmail.com>
 * @since  Sun Apr 12 20:36:50 IST 2015
 *
 * @package Kayako\Twitter
 */
class TwitterService
{
    /**
     * Relative path of the API that provides search
     */
    const TWITTER_SEARCH_API = 'search/tweets';

    /**
     * Twitter API OAuth Interface
     *
     * @var \Abraham\TwitterOAuth\TwitterOAuth
     */
    private $twitterOAuth;

    /**
     * Cache Service handle 
     *
     * @var TwitterCacheRedis
     */
    private $cache;
    
    /**
     * Constructor
     *
     * @param \Abraham\TwitterOAuth\TwitterOAuth $twitterOAuth
     * @param TwitterCacheRedis $redis
     */
    public function __construct(\Abraham\TwitterOAuth\TwitterOAuth $twitterOAuth, TwitterCacheRedis $redis)
    {
        $this->twitterOAuth = $twitterOAuth;
        $this->cache        = $redis;
    }

    /**
     * Processes parameter $maxId to reduce redundant tweets
     *
     * Since the maxId parameter is inclusive, the Tweet with the matching ID in last API call 
     * will actually be returned again in the next call. So to handle this, we need to subtract 1 from the 
     * maxId. 
     *
     * Since Twitter maxId is 64 bit, so the subtration can only be done in environments where Integers
     * can be pepresented in 64 bit, Else we should skip the subtraction. 
     *
     * @param string $maxId The ID to be processed
     * @return mixed int/string 
     */
    protected function processMaxId($maxId)
    {
        return (PHP_INT_SIZE === 8 && (empty($maxId) === false)) ? ($maxId - 1) : $maxId;
    }

    /**
     * Validates the Search Parameters
     *
     * @param  array $searchParams
     * @throws InvalidArgumentException
     * @return bool 
     */
    private function validateSearchParams(array $searchParams)
    {
        $requiredKeys = array('query', 'fetchCount', 'maxId');

        // Required Param validations
        foreach ($requiredKeys as $key) {
            if (array_key_exists($key, $searchParams) === false) {
                throw new \InvalidArgumentException(sprintf("Search Param '%s' is required.", $key)); 
            }
        }

        // Param's value validation
        if(strlen(trim($searchParams['query'])) < 1) {
            throw new \InvalidArgumentException("Search Query cannot be empty."); 
        } elseif (!(is_numeric($searchParams['fetchCount']) === true && (int) $searchParams['fetchCount'] >= 15 && (int) $searchParams['fetchCount'] <= 100)) {
            throw new \InvalidArgumentException("Twitter FetchCount should be between 15 and 100."); 
        }

        return true;
    }

    /**
     * Returns the next set of tweets either from cache or via the Twiteer Search API
     *
     * @param  array $searchParams Parameters required for the Search like
     *  <code>
     *       array(
     *            'query'       => '#custserv',
     *            'fetchCount'  => 100,
     *            'maxId'       => ,
     *            'filters'     => array(
     *                'minReTweet'  => 1
     *            )  
     *       );
     *  </code>
     *
     * @param int $limit
     * @return array
     */
    public function getTweets(array $searchParams, $limit = 20)
    {
        $this->validateSearchParams($searchParams);

        $maxId    = $this->processMaxId($searchParams['maxId']);
        $ns       = $this->getCacheNamespace($searchParams['query']);

        // If maxId is null then search twitter using sinceId, else try to get from cache
        // or from twitter using maxId 
        if (is_null($maxId) === true) {
            // Make an API call using sinceId, to get the Latest tweets
            return $this->fetchTweets($maxId, $this->cache->getMaximumTweetId($ns), $searchParams, $limit);
        } else {
            // First, search the cache from tweets
            $tweets = $this->cache->getTweets($ns, $maxId, $limit);
            
            if (count($tweets) === $limit) {
                return array(
                    'status' => true,
                    'tweets' => $tweets,
                );
            } else {
                // Make an API call using maxId, to get the tweet less than maxId
                return $this->fetchTweets($this->cache->getMinimumTweetId($ns), null, $searchParams, $limit);
            }
        }
    }

    /**
     * Fetch Tweets by using Twitter Search API, cache the Result and returns
     * cached response
     *
     * @param int $maxId maxId to get tweets less than this
     * @param int $sinceId sinceId to get the newer tweets than sinceId  
     * @param array $searchParams parameters to make a actual search Call like query and fetchCount
     * @param int $limit no of tweets to return.
     *
     * @return array 
     */
    protected function fetchTweets($maxId, $sinceId, $searchParams, $limit)
    {
        $response = array();

        try {
            // Make the API call using given params
            $apiResult = $this->twitterOAuth->get(
                self::TWITTER_SEARCH_API,
                array(
                    'q'         => $searchParams['query'],
                    'count'     => $searchParams['fetchCount'],
                    'max_id'    => $maxId,
                    'since_id'  => $sinceId
                )
            );

            $response['status'] = true;
            $response['tweets'] = array();

            // Apply Search Filters, If any
            $tweets   = $this->applySearchFilter($searchParams, (array) @$apiResult->statuses); 
            $ns       = $this->getCacheNamespace($searchParams['query']);
            
            // Add tweets to the cache
            foreach ($tweets as $tweet) {
                $tweetData = array(
                    'id_str'        => $tweet->id_str,
                    'text'          => $tweet->text,
                    'screen_name'   => $tweet->user->screen_name,
                    'name'          => $tweet->user->name,
                    'retweet_count' => $tweet->retweet_count
                );
                
                // Add to cache
                $this->cache->addTweet($ns, $tweet->id, $tweetData);
            }
            
            // Finally, populate the response back from cache
            $response['tweets'] = $this->cache->getTweets($ns, $maxId, $limit);
        } catch (\Exception $e) {
            // TODO: Log the Exception 
            $response['status'] = false;
            $response['error']  = $e->getMessage();
        }

        return $response;
    }

    /**
     * Apply Post Search Filters to further narrow down the result set
     * This is a simple filter mechanism that is limited to predefined filters But 
     * can be extended to a full fledge complex filtering like suporting AND OR NOT conditions, 
     * filtering using user callback etc.
     *
     * @param array $searchParams The origianl search Params used to make a API call having filters 
     * @param array $tweets Tweets to filter 
     *
     * return array filtered tweets 
     */
    protected function applySearchFilter(array $searchParams, array $tweets)
    {
        if (array_key_exists('filters', $searchParams) === true) {
            foreach ($searchParams['filters'] as $field => $value) {
                switch ($field) {
                    case 'minReTweet':
                        // Minimum Re-Tweet filter
                        $tweets = array_filter($tweets, function($tweet) use ($value) {
                            return ((int) $tweet->retweet_count >= (int) $value); 
                        }); 
                        break;
                        // Add more Filters here
                    default:
                        // NOT SUPPORTED YET
                        // We may throw an Exception straight away to be more strict in search or fail silently
                        // $this->log->error('Unsupported Filter field ' . var_export(array($field, $value), true));
                        continue;
                }
            }
        }

        return $tweets;
    } 
    
    /**
     * Create a tweeter Search Cache Namespace prefixed with 'tweet'.
     * Having Query Attributes in Cahce Namespace enable us to have separate caching set 
     * for every search.
     *
     * @param string $query
     * @return string The Tweet Search Namespace
     */
    protected function getCacheNamespace($query)
    {
        // TODO: This needs rethink
        return 'tweetns_' . md5($query);
    }
}
