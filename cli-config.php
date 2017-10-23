<?php namespace JobLion\Database;

/**
 * Doctrine commandline config, for migrating the database
 */

require_once __DIR__."/src/autoload.php";

// load config file
$configFile = new ConfigFile();
$configFile->load();

// create doctrine entity manager
$entityManager = EntityManager::create($configFile);

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
