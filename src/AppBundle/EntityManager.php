<?php namespace JobLion\AppBundle;

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
          [
            __DIR__."/Entity",
            __DIR__."/../ExperienceReportBundle/Entity",
            __DIR__."/../JobCategoryBundle/Entity"
          ],
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

        // use in memory database if testing
        if ($isTest && $configFile->get($prefix . "Host") == ":memory:") {
            $connectionParams = array(
              'url' => 'sqlite:///:memory:',
            );
        }

        return ORM\EntityManager::create($connectionParams, $entityConfig);
    }
}
