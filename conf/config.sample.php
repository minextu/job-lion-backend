<?php
$CONFIG = array(

/**
* If set to false, 500 messages are displayed instead of debug messages
*/
'isDebug' => true,

/**
* The host of the Database
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
*  The database name
*/
'dbDatabase' => '',

/**
*  The current state of the Database.
*  This variable will be updated after every migration and should not be changed manually
*  Every migration script greater than databse_version will be started, till database_target_version is reached.
*/
'dbVersion' => 0,

/**
* The targeted Migration Status.
* Every migration script greater than databse_version will be started, till database_target_version is reached.
* If set to true, the newest Migration will be used
*/
'dbTargetVersion' => true,

/**
* The host of the Test Database
*/
'testDbHost' => '',

/**
* The username of the Test Database
*/
'testDbUser' => '',

/**
* The password of the Test Database
*/
'testDbPassword' => '',

/**
*  The test database name
*/
'testDbDatabase' => '',

);
