<?php namespace JobLion\AppBundle;

use Silex\Application as Silex;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use JobLion\AppBundle\ConfigFile;
use Doctrine\ORM\EntityManager;

use JobLion\JobCategoryBundle;
use JobLion\ExperienceReportBundle;

/**
 * Initializes all api routes for all bundles
 */
class AppBundle
{
    /**
     * All bundles to init routes for
     * @var array
     */
    public static $enabledBundles = [
      "ExperienceReportBundle",
      "JobCategoryBundle"
    ];

    /**
     * Init all api routes and return the silex Application
     * @param  EntityManager $entityManager  Database entites to be used
     * @param  ConfigFile    $configFile     Config file to be used
     * @return Silex                         Silex Application
     */
    public static function init(EntityManager $entityManager, ConfigFile $configFile)
    {
        $app = new Silex();
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new SessionServiceProvider());
        $app['debug'] = $configFile->get('isDebug');

        // User routes
        $app['user.controller'] = function () use ($entityManager, $app) {
            return new Controller\User($entityManager, $app);
        };
        $app->post('/v1/user/create', "user.controller:create");
        $app->post('/v1/user/login', "user.controller:login");
        $app->post('/v1/user/logout', "user.controller:logout");
        $app->get('/v1/user/info', "user.controller:info");

        // init all bundles
        foreach (self::$enabledBundles as $bundle) {
            $bundleClass = "JobLion\\$bundle\\$bundle";
            $bundleClass::init($entityManager, $configFile, $app);
        }

        return $app;
    }
}
