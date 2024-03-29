<?php namespace JobLion\AppBundle;

use Silex\Application as Silex;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use JobLion\AppBundle\ConfigFile;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM;

/**
 * Can init this app
 */
class AppBundle
{
    /**
     * All bundles to init routes for
     * @var array
     */
    public static $enabledBundles = [
      "AppBundle",
      "AuthBundle",
      "ExperienceReportBundle",
      "CommentBundle",
      "CompanyBundle"
    ];

    /**
     * Init all api routes for this bundle
     * @param  EntityManager $entityManager  Database entites to be used
     * @param  ConfigFile    $config         Config file to be used
     * @param  Silex         $app            Silex Application
     */
    public static function setRoutes(EntityManager $entityManager, ConfigFile $config, Silex &$app)
    {
        // Job Category routes
        $app['jobCategory.controller'] = function () use ($entityManager, $app, $config) {
            return new Controller\JobCategoryController($entityManager, $app, $config);
        };
        $app->post('/v1/jobCategories/', "jobCategory.controller:create");
        $app->get('/v1/jobCategories/', "jobCategory.controller:list");
        $app->get('/v1/jobCategories/{id}', "jobCategory.controller:get");
        $app->delete('/v1/jobCategories/{id}', "jobCategory.controller:delete");
    }

    /**
     * Init api routes for all bundles and return the silex Application
     * @param  EntityManager $entityManager  Database entites to be used
     * @param  ConfigFile    $configFile     Config file to be used
     * @return Silex                         Silex Application
     */
    public static function init(EntityManager $entityManager, ConfigFile $configFile)
    {
        $app = new Silex();
        $app->register(new ServiceControllerServiceProvider());
        $app['debug'] = $configFile->get('isDebug');
        $app['isTest'] = false;

        // support json requests
        $app->before(function (Request $request) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });

        // init all bundles
        foreach (self::$enabledBundles as $bundle) {
            $bundleClass = "JobLion\\$bundle\\$bundle";
            $bundleClass::setRoutes($entityManager, $configFile, $app);
        }

        return $app;
    }

    /**
     * Crete doctrine entity manager instance using a config file
     * @param  ConfigFile $configFile Config file to be used
     * @param  boolean    $isTest     Wether to use the test database or not
     * @return ORM\EntityManager      Doctrine EntityManager
     */
    public static function createEntityManager(ConfigFile $configFile, bool $isTest=false)
    {
        // get entity folder for all bundles
        $entityFolders = [];
        foreach (self::$enabledBundles as $bundle) {
            $folder =  __DIR__."/../$bundle/Entity";

            if (is_dir($folder)) {
                $entityFolders[] = $folder;
            }
        }

        $entityConfig = Setup::createAnnotationMetadataConfiguration(
          $entityFolders,
          $configFile->get('isDebug')
        );
        $entityConfig->setAutoGenerateProxyClasses(true);

        // database configuration
        $prefix = $isTest ? "testDb" : "db";

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
