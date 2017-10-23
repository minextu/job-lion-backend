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

        $app->post('/api/v1/user/create', "user.controller:create");
        $app->post('/api/v1/user/login', "user.controller:login");
        $app->post('/api/v1/user/logout', "user.controller:logout");
        $app->get('/api/v1/user/info', "user.controller:info");

        return $app;
    }
}
