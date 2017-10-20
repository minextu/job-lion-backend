<?php namespace JobLion\Api;

use JobLion\Database\Backend\BackendInterface;
use Silex\Application as Silex;

/**
 * Controller service for silex
 */
abstract class AbstractApi
{
    /**
     * Database backend to be used
     * @var BackendInterface
     */
    protected $db;
    /**
     * Silex Application
     * @var Silex
     */
    protected $app;

    public function __construct(BackendInterface $db, Silex $app)
    {
        $this->db = $db;
        $this->app = $app;
    }
}
