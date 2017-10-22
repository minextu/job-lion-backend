<?php namespace JobLion\Database;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM;

/**
 * Can create a doctrine EntityManager
 */
class EntityManager
{
    /**
     * Crete doctrine entity manager instance using a config file
     * @param  ConfigFile $configFile Config file to be used
     * @param  boolean    $isTest     Wether to use the test database or not
     * @return ORM\EntityManager      Doctrine EntityManager
     */
    public static function create(ConfigFile $configFile, bool $isTest=false)
    {
        // load entity configs
        $entityConfig = Setup::createAnnotationMetadataConfiguration(
          array(__DIR__."/Entity"),
          $configFile->get('isDebug')
        );

        $prefix = $isTest ? "testDb" : "db";

        // database configuration
        $connectionParams = array(
          'dbname' => $configFile->get($prefix . "Database"),
          'user' => $configFile->get($prefix . "User"),
          'password' => $configFile->get($prefix . "Password"),
          'host' => $configFile->get($prefix . "Host"),
          'driver' => 'pdo_mysql',
        );

        return ORM\EntityManager::create($connectionParams, $entityConfig);
    }
}
