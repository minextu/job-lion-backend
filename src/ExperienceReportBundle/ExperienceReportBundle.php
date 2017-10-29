<?php namespace JobLion\ExperienceReportBundle;

use Silex\Application as Silex;
use JobLion\AppBundle\ConfigFile;
use Doctrine\ORM\EntityManager;

/**
 * Can Initialize all api routes for this bundle
 */
class ExperienceReportBundle
{
    /**
     * Init all api routes for this bundle
     * @param  EntityManager $entityManager  Database entites to be used
     * @param  ConfigFile    $configFile     Config file to be used
     * @param  Silex         $app            Silex Application
     */
    public static function setRoutes(EntityManager $entityManager, ConfigFile $config, Silex &$app)
    {
        $app['experienceReport.controller'] = function () use ($entityManager, $app) {
            return new Controller\ExperienceReport($entityManager, $app);
        };
        $app->post('/v1/experienceReport/create', "experienceReport.controller:create");
        $app->get('/v1/experienceReport/list', "experienceReport.controller:list");
    }
}
