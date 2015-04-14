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

use Kayako\Util;

/**
 * Provisioning layer for Twitter Service to facilitate 
 * Caching operations. 
 *
 * @author Mukesh Sharma <cogentmukesh@gmail.com>
 * 
 * @uses    \Predis\Client
 * @package  Kayako\Twitter
 */
class TwitterCacheRedis
{
    /**
     * Cache Key Prefix 
     *
     * @var String
     */
    private $cacheKeyPrefix = 'tweet_';
    
    /**
     * Client to interact with Redis server
     *
     * @var \Predis\Client
     */
    private $client;

    /**
     * Class Constructor
     */
    public function __construct(\Predis\Client $redis)
    {
        $this->client = $redis;
    }

    /**
     * Add Tweets to given namespace Redis
     *
     * @param string $ns      Cache Namespace or Key for the Sorted Set
     * @param int    $tweetId id of the tweet to be stored
     * @param array  $data    Twitter Data to cache
     *
     * @throw  \InvalidArgumentException
     * @return array
     */
    public function addTweet($ns, $tweetId, array $data)
    {
        if (Util::isValidString($ns) === false) {
            throw new \InvalidArgumentException('Cache Namespace cannot be empty.');
        } elseif (Util::isValidString($tweetId) === false) {
            throw new \InvalidArgumentException('TweetId cannot be empty.');
        } 

        // We are maintaing an ordered set of tweetIds and Hash of the Tweet Data
        $this->client->zadd($ns, array($tweetId => $tweetId));
        $this->client->hmset($this->cacheKeyPrefix. '#' . $tweetId, $data);

        return true;
    }

    /**
     * Returns the Minimum TweetId of the given namespace from Redis
     *
     * @param string $ns 
     *
     * @throws \InvalidArgumentException
     * @return int minimum tweet id
     */
    public function getMinimumTweetId($ns)
    {
        if (Util::isValidString($ns) === false) {
            throw new \InvalidArgumentException('Cache Namespace cannot be empty.');
        }

        if ($this->getTotalTweetCount($ns) === 0) {
            return 0;
        } else {
            return $this->client->zrange($ns, 0, 0)[0];
        }
    }

    /**
     * Returns the Maximum TweetId of the given namespace from Redis
     *
     * @param string $ns 
     *
     * @throws \InvalidArgumentException
     * @return int maximum tweet id
     */
    public function getMaximumTweetId($ns)
    {
        if (Util::isValidString($ns) === false) {
            throw new \InvalidArgumentException('Cache Namespace cannot be empty.');
        }
        
        if ($this->getTotalTweetCount($ns) === 0) {
            return 0;
        } else {
            return $this->client->zrevrange($ns, 0, 0)[0];
        }
    }

    /**
     * Return the total no of tweets of the given namespace
     *
     * @param string $ns 
     *
     * @throws \InvalidArgumentException
     * @return int total count
     */
    public function getTotalTweetCount($ns)
    {
        if (Util::isValidString($ns) === false) {
            throw new \InvalidArgumentException('Cache Namespace cannot be empty.');
        }
        
        return $this->client->zcard($ns);
    }

    /**
     * Return all tweets of a namespace whose Id is less than given maxId
     *
     * @param string $ns Cache Namespace
     * @param int $maxId 
     * @param int $limit 
     *
     * @throws \InvalidArgumentException
     * @return array 
     */
    public function getTweets($ns, $maxId, $limit)
    {
        if (Util::isValidString($ns) === false) {
            throw new \InvalidArgumentException('Cache Namespace cannot be empty.');
        } elseif (is_numeric($limit) === false || $limit < 1) {
            throw new \InvalidArgumentException('Limit must be a valid positive number.');
        } 
        
        $result = array();
        $maxId  = $maxId ? $maxId : '+inf';
        $tweets = $this->client->zrevrangebyscore($ns, $maxId, '-inf', array('limit' => array('offset' => 0, 'count' => $limit)));

        foreach ($tweets as $tweet) {
            $result[] = $this->client->hgetall($this->cacheKeyPrefix . '#' . $tweet);
        }

        return $result;
    }

    /**
     * Delete the given tweetId of the given namespace
     *
     * @param string $ns 
     * @param int    $tweetId TweetId to delete
     *
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function deleteTweet($ns, $tweetId)
    {
        if (Util::isValidString($ns) === false) {
            throw new \InvalidArgumentException('Cache Namespace cannot be empty.');
        } elseif (Util::isValidString($tweetId) === false) {
            throw new \InvalidArgumentException('TweetId cannot be empty.');
        } 
        
        $this->client->zrem($ns, $tweetId);
        $this->client->del($this->cacheKeyPrefix . '#' . $tweetId);

        return true;
    }
}
