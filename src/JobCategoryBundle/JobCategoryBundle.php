<?php namespace JobLion\JobCategoryBundle;

use Silex\Application as Silex;
use JobLion\AppBundle\ConfigFile;
use Doctrine\ORM\EntityManager;

/**
 * Can Initialize all api routes for this bundle
 */
class JobCategoryBundle
{
    /**
     * Init all api routes for this bundle
     * @param  EntityManager $entityManager  Database entites to be used
     * @param  ConfigFile    $configFile     Config file to be used
     * @param  Silex         $app            Silex Application
     */
    public static function init(EntityManager $entityManager, ConfigFile $config, Silex &$app)
    {
        $app['jobCategory.controller'] = function () use ($entityManager, $app) {
            return new Controller\JobCategory($entityManager, $app);
        };
        $app->post('/v1/jobCategory/create', "jobCategory.controller:create");
        $app->get('/v1/jobCategory/list', "jobCategory.controller:list");
    }
}
