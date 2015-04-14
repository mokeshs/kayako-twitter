Kayako-Twitter
==============

A simple Twitter API client in PHP that fetches and displays Tweets those 
* a) Have been re-Tweeted at least once and 
* b) Contain the hashtag #custserv


### Highlights

* Built on the shoulders of Silex (PHP Microframework) in MVC style
* Provides lazy loading of Tweets with infinite (almost) scroll
* Caching support via Redis
* Configurable Search Terms and Filters
* Easy to extend
* Supports powerful templating using Twig

### Dependencies

* `PHP >= 5.3`
* Redis Server

### Installation
* Install Composer
    ```
     curl -sS https://getcomposer.org/installer | php
     mv composer.phar /usr/local/bin/composer
    ```
* Clone or Download the Source Code Zip.
* Extract and Point Document Root to `kayako-twitter/web`
* Run `php composer.phar install` / `composer install` in `kayako-twitter` directory
* Add Twitter API credentials  in ``src/config/config.php``
* Run redis-server with default config
* Open browser and navigate to localhost
* All the tweets will be displayed

### Testing
* Run `phpunit` OR `vendor/phpunit/phpunit/phpunit` in source directory home
* Test Coverage Reports will be available in ``build`` directory

### Known Issue
Nothing at the moment :)

## Contributors

 0. Mukesh Sharma
