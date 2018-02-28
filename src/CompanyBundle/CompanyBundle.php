<?php namespace JobLion\CompanyBundle;

use Silex\Application as Silex;
use JobLion\AppBundle\ConfigFile;
use Doctrine\ORM\EntityManager;

/**
 * Can Initialize all api routes for this bundle
 */
class CompanyBundle
{
    /**
     * Init all api routes for this bundle
     * @param  EntityManager $entityManager  Database entites to be used
     * @param  ConfigFile    $config         Config file to be used
     * @param  Silex         $app            Silex Application
     */
    public static function setRoutes(EntityManager $entityManager, ConfigFile $config, Silex &$app)
    {
        $app['company.controller'] = function () use ($entityManager, $app, $config) {
            return new Controller\CompanyController($entityManager, $app, $config);
        };
        $app->post('/v1/companies/', "company.controller:create");
        $app->get('/v1/companies/', "company.controller:list");
        $app->get('/v1/companies/{id}', "company.controller:get");
    }
}
