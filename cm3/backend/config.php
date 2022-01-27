<?php

/* Time zone PHP should use for date calculations (e.g. when badges are available). */
date_default_timezone_set('America/Los_Angeles');

/* This is the default configuration for CONcrescent. Replace all values in this file. */
return array(

    /* Database Configuration. Currently only MySQL 7+ (and compatible) is supported */
    'database' => array(

        /* Host name or IP address of the MySQL server. Typically 'localhost' or '127.0.0.1'. */
        'host' => 'localhost',
        'username' => 'cm_user',
        'password' => 'cm_pass',

        /* Name of the MySQL database to use for this application. */
        'database' => 'cm3_db',
        'prefix' => 'cm_',
        /* Time zone MySQL should use for date calculations (e.g. when badges are available). */
        'timezone' => 'SYSTEM',
    ),
    'environment' => array(
      //If not installed in the root, specify it here.
      //No trailing slash!
      'base_path' => '/concrescent/cm3/backend',
      //Should responses be GZipped?
      'use_gzip' => true
    ),
    'error' => array(

      // Should be set to false for the production environment.
      //If true API requests may contain HTML!
      'display_error_details' => true,
      // Should be set to false for the test environment
      'log_errors' => true,
      // Display error details in error log
      'log_error_details' => true,
    ),
    'logger' => array(
      'name' => 'app',
      'path' => dirname(__FILE__) .'/logs',
      'filename' => 'app.log',
      'level' => \Monolog\Logger::INFO,
      'file_permission' => 0775,
    )
);
