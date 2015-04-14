<?php

use Kayako\KayakoPlatformApplication;

/**
 * Front Controller for the App.
 *   - Bootstrap the Application
 *   - Handles the Request
 *   - Render Response
 *
 * @author Mukesh Sharma <cogentmukesh@gmail.com>
 * @since  Mon Mar 30 22:37:16 IST 2015
 * 
 * “What you do makes a difference, and you have to decide 
 *  what kind of difference you want to make.”
 *
 * Copyright (c) 2015 Mukesh Sharma <cogentmukesh@gmail.com>
 */

/**
 * Setup Class Loading via composer Autoloader 
 */
require_once __DIR__.'/../vendor/autoload.php'; 

/**
 * Create Platform Application 
 * TODO: Configure Debug Param as per the ENV and initiate dynamically
 */
$app = new KayakoPlatformApplication(array(
    'config' => require(realpath(__DIR__) . '/../src/config/config.php')
));

$app->run();


