<?php namespace JobLion\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use JobLion\AppBundle\Entity\User;
use Silex\Application as Silex;

/**
 * Controller service for silex
 */
abstract class AbstractController
{
    /**
     * Database entities to be used
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * Silex Application
     * @var Silex
     */
    protected $app;

    /**
     * @param EntityManager $entityManager Doctrine EntityManager
     * @param Silex         $app           Silex Application
     */
    public function __construct(EntityManager $entityManager, Silex $app)
    {
        $this->entityManager = $entityManager;
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

        $user = $this->entityManager->find(User::class, $id);
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
