<?php namespace JobLion\CommentBundle;

use Silex\Application as Silex;
use JobLion\AppBundle\ConfigFile;
use Doctrine\ORM\EntityManager;

/**
 * Can Initialize all api routes for this bundle
 */
class CommentBundle
{
    /**
     * Init all api routes for this bundle
     * @param  EntityManager $entityManager  Database entites to be used
     * @param  ConfigFile    $config         Config file to be used
     * @param  Silex         $app            Silex Application
     */
    public static function setRoutes(EntityManager $entityManager, ConfigFile $config, Silex &$app)
    {
        $app['comment.controller'] = function () use ($entityManager, $app, $config) {
            return new Controller\CommentController($entityManager, $app, $config);
        };

        $app->post('/v1/experienceReport/{experienceReportId}/comments', "comment.controller:create");
        $app->get('/v1/experienceReport/{experienceReportId}/comments', "comment.controller:list");
    }
}
