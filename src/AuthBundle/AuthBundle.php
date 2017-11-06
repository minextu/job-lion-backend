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
        $app['auth.controller'] = function () use ($entityManager, $app) {
            return new Controller\Auth($entityManager, $app);
        };
        $app->post('/v1/auth/register', "auth.controller:register");
        $app->post('/v1/user/login', "auth.controller:login");
        $app->post('/v1/user/logout', "auth.controller:logout");
        $app->get('/v1/user/info', "auth.controller:info");
    }
}
