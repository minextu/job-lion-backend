<?php namespace JobLion\AuthBundle;

use Silex\Application as Silex;
use JobLion\AppBundle\ConfigFile;
use Doctrine\ORM\EntityManager;

/**
 * Can Initialize all api routes for this bundle
 */
class AuthBundle
{
    /**
     * Init all api routes for this bundle
     * @param  EntityManager $entityManager  Database entites to be used
     * @param  ConfigFile    $configFile     Config file to be used
     * @param  Silex         $app            Silex Application
     */
    public static function setRoutes(EntityManager $entityManager, ConfigFile $config, Silex &$app)
    {
        // Auth routes
        $app['auth.controller'] = function () use ($entityManager, $app, $config) {
            return new Controller\AuthController($entityManager, $app, $config);
        };
        $app->post('/v1/auth/register', "auth.controller:register");
        $app->post('/v1/auth/login', "auth.controller:login");
        $app->get('/v1/auth/info', "auth.controller:info");
    }
}
