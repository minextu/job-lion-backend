<?php namespace JobLion;

require_once __DIR__.'/../src/autoload.php';

// load config file
$configFile = new Database\ConfigFile();
$configFile->load();

// create doctrine entity manager (access to database)
$entityManager = Database\EntityManager::create($configFile);

// run api
$app = Api\App::init($entityManager, $configFile);
$app->run();
