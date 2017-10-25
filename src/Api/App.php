<?php namespace JobLion\Api;

use Silex\Application as Silex;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use JobLion\Database\Backend\BackendInterface;
use JobLion\Database\ConfigFile;
use Doctrine\ORM\EntityManager;

/**
 * Initializes all api routes
 */
class App
{
    /**
     * Init all api routes and return the silex Application
     * @param  EntityManager $entityManager  Database entites to be used
     * @param  ConfigFile    $configFile     Config file to be used
     * @return Silex                         Silex Application
     */
    public static function init(EntityManager $entityManager, ConfigFile $config)
    {
        $app = new Silex();
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new SessionServiceProvider());
        $app['debug'] = $config->get('isDebug');

        // User routes
        $app['user.controller'] = function () use ($entityManager, $app) {
            return new Controller\User($entityManager, $app);
        };
        $app->post('/v1/user/create', "user.controller:create");
        $app->post('/v1/user/login', "user.controller:login");
        $app->post('/v1/user/logout', "user.controller:logout");
        $app->get('/v1/user/info', "user.controller:info");

        // JobCategory routes
        $app['jobCategory.controller'] = function () use ($entityManager, $app) {
            return new Controller\JobCategory($entityManager, $app);
        };
        $app->post('/v1/jobCategory/create', "jobCategory.controller:create");
        $app->get('/v1/jobCategory/list', "jobCategory.controller:list");

        // Report routes
        $app['experienceReport.controller'] = function () use ($entityManager, $app) {
            return new Controller\ExperienceReport($entityManager, $app);
        };
        $app->post('/v1/experienceReport/create', "experienceReport.controller:create");
        $app->get('/v1/experienceReport/list', "experienceReport.controller:list");

        return $app;
    }
}
