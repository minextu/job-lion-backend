<?php namespace JobLion\Api;

use Silex\Application as Silex;
use Silex\Provider\ServiceControllerServiceProvider;
use JobLion\Database\Backend\BackendInterface;
use JobLion\Database\Config;

/**
 * Initializes all api routes
 */
class App
{
    /**
     * Init all api routes and return the silex Application
     * @param  BackendInterface $db     Database backend to be used
     * @param  Config           $config Config object
     * @return Silex                    Silex Application
     */
    public static function init(BackendInterface $db, Config $config)
    {
        $app = new Silex();
        $app->register(new ServiceControllerServiceProvider());
        $app['debug'] = $config->get('isDebug');

        // User routes
        $app['user.controller'] = function () use ($db, $app) {
            return new User($db, $app);
        };
        $app->post('/api/v1/user/create', "user.controller:create");

        return $app;
    }
}
