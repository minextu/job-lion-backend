<?php namespace JobLion\Api;

use Silex\Application as Silex;
use JobLion\Database\Backend\BackendInterface;
use JobLion\Database\Config;

class App
{
    public static function init(BackendInterface $db, Config $config)
    {
        $app = new Silex();
        $app['debug'] = $config->get('isDebug');

        $app->mount("/api/v1/user", include 'user.php');

        return $app;
    }
}
