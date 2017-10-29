<?php namespace JobLion;

require_once __DIR__.'/../src/autoload.php';

// load config file
$configFile = new AppBundle\ConfigFile();
$configFile->load();

// create doctrine entity manager (access to database)
$entityManager = AppBundle\EntityManager::create($configFile);

// run api
$app = AppBundle\AppBundle::init($entityManager, $configFile);
$app->run();
