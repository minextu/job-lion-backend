<?php namespace JobLion;

require_once __DIR__.'/src/autoload.php';

use JobLion\Database\Config;
use JobLion\Database\Backend;

// load config
$config = new Config();
$config->load();

// connect to database
$db = new Backend\Mysql(
    $config->get("dbHost"),
    $config->get("dbUser"),
    $config->get("dbPassword"),
    $config->get("dbDatabase")
);

// run api
$app = Api\App::init($db, $config);
$app->run();
