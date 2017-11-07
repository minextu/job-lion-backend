<?php
$CONFIG = array(

/**
* If set to false, 500 messages are displayed instead of debug messages
*/
'isDebug' => true,

/**
* The host of the Mysql Database
*/
'dbHost' => '',

/**
* The username of the Database
*/
'dbUser' => '',

/**
* The password of the Database
*/
'dbPassword' => '',

/**
* The database name
*/
'dbDatabase' => '',

/**
 * Server key for user authentication using json web tokens
 * Generate using `php -r "echo base64_encode(openssl_random_pseudo_bytes(64));"`
 */
'jwtKey' => '',

/**
* The host of the Test Mysql Database (:memory: for fast in memory testing)
*/
'testDbHost' => ':memory:',

/**
* The username of the Test Database (leave blank if using :memory:)
*/
'testDbUser' => '',

/**
* The password of the Test Database (leave blank if using :memory:)
*/
'testDbPassword' => '',

/**
* The test database name (leave blank if using :memory:)
*/
'testDbDatabase' => '',

);
