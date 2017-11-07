<?php namespace JobLion\AuthBundle;

use JobLion\AppBundle\ConfigFile;
use JobLion\AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;

/**
 * Generate and parse json web tokens for user login
 */
class Token
{
    /**
     * Config file object to read key from
     * @var ConfigFile
     */
    private $configFile;

    /**
     * Key to encode token
     * @var String
     */
    private $key;

    /**
     * Doctrine entity manager
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param ConfigFile    $configFile     Config file to use
     * @param EntityManager $entityManager  Doctrine entity manager
     */
    public function __construct(ConfigFile $configFile, EntityManager $entityManager)
    {
        $this->configFile = $configFile;
        $this->entityManager = $entityManager;

        // generate key if not set
        if (empty($configFile->get('jwtKey'))) {
            $key = base64_encode(openssl_random_pseudo_bytes(64));
            $configFile->set('jwtKey', $key);
        }

        // get key from config file
        $this->key = base64_decode($configFile->get('jwtKey'));
    }

    /**
     * Generate login token from user object
     * @param  User   $user User to generate token for
     * @return String       Generated token
     */
    public function generate(User $user, $validSeconds=60*60*24) : String
    {
        $tokenId    = base64_encode(random_bytes(32));
        $issuedAt   = time();
        $notBefore  = $issuedAt;
        $expire     = $notBefore + $validSeconds;
        $serverName = gethostname();

        // create token as array
        $data = [
              'iat'  => $issuedAt,
              'jti'  => $tokenId,
              'iss'  => $serverName,
              'nbf'  => $notBefore,
              'exp'  => $expire,
              'data' => [
                  'userId'   => $user->getId()
              ]
          ];

        // encode token using the secret key
        $token = JWT::encode($data, $this->key, 'HS512');

        return $token;
    }

    /**
     * Get User from token
     * @param  String $token Token to user
     * @return User          User that belongs to this token
     */
    public function getUser(String $token) : User
    {
        try {
            // extract user id out of token
            $token = JWT::decode($token, $this->key, array('HS512'));
            $userId = $token->data->userId;

            // find user entity
            $user = $this->entityManager->find(User::class, $userId);
            return $user;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
