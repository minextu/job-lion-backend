<?php namespace JobLion\Api;

use Silex\Application as Silex;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
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
        $app->register(new SessionServiceProvider());
        $app['debug'] = $config->get('isDebug');

        // User routes
        $app['user.controller'] = function () use ($db, $app) {
            return new Controller\User($db, $app);
        };

        $app->post('/api/v1/user/create', "user.controller:create");
        $app->post('/api/v1/user/login', "user.controller:login");
        $app->post('/api/v1/user/logout', "user.controller:logout");
        $app->get('/api/v1/user/info', "user.controller:info");

        return $app;
    }
}
