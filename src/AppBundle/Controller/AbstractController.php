<?php namespace JobLion\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use JobLion\AppBundle\Entity\User;
use JobLion\AppBundle\ConfigFile;
use Silex\Application as Silex;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use JobLion\AuthBundle\Token;
use JobLion\AuthBundle;

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
     * Config file object to use
     * @var ConfigFile
     */
    protected $configFile;

    /**
     * User that is making this request
     * @var User
     */
    protected $user;

    /**
     * @param EntityManager $entityManager Doctrine EntityManager
     * @param Silex         $app           Silex Application
     * @param ConfigFile    $config        Config file to use
     */
    public function __construct(EntityManager $entityManager, Silex $app, ConfigFile $config)
    {
        $this->entityManager = $entityManager;
        $this->app = $app;

        $this->configFile = $config;
    }

    /**
     * @apiDefine Login
     * @apiParam {String}  jwt          Login token
     * @apiError           NotLoggedIn  You are not logged in
     */

    /**
     * Check if user is logged in using jwt token
     * @param  Request $request  Info about this request
     * @return JsonResponse      Error response when token is invalid, Empty otherwise
     */
    public function requireLogin(Request $request)
    {
        try {
            // extract jwt token
            $tokenString = $request->get('jwt');
            if (empty($tokenString)) {
                throw new AuthBundle\Exception("No jwt token provided");
            }

            // extract user out of token
            $token = new Token($this->configFile, $this->entityManager);
            $this->user = $token->getUser($tokenString);
            return;
        } catch (AuthBundle\Exception $e) {
            return $this->app->json(
              ["error" => "NotLoggedIn", "errorMessage" => $e->getMessage()],
              401
            );
        }
    }
}