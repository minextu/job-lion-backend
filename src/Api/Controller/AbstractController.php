<?php namespace JobLion\Api\Controller;

use JobLion\Database\Backend\BackendInterface;
use JobLion\Database\Account\User;
use Silex\Application as Silex;

/**
 * Controller service for silex
 */
abstract class AbstractController
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

    /**
     * Get current user
     * @return User|bool False if not logged in, Current user object otherwise
     */
    protected function getLogin()
    {
        $id = $this->app['session']->get('userId');

        // stop if not logged in
        if ($id === null) {
            return false;
        }

        $user = new User($this->db, $id);
        return $user;
    }

    /**
     * Set given user object as current user
     * @param User $user User object to login
     */
    protected function loginUser(User $user)
    {
        $this->app['session']->set("userId", $user->getId());
    }

    /**
     * Logout the current user
     */
    protected function logoutUser()
    {
        $this->app['session']->remove("userId");
    }
}
