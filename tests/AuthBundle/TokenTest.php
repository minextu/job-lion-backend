<?php namespace JobLion\AuthBundle;

use JobLion\AppBundle\Entity\User;
use JobLion\AppBundle\ConfigFile;
use Firebase\JWT\JWT;
use JobLion\AbstractJobLionApiTest;

class TokenTest extends AbstractJobLionApiTest
{
    public function testTokenCanBeGeneratedAndParsedAgain()
    {
        $testUser = $this->createTestUser();

        // generate login token for user
        $token = new Token($this->getConfigFile(), $this->getEntityManager());
        $tokenString = $token->generate($testUser);

        // get user from token
        $user = $token->getUser($tokenString);

        // check if correct user was parsed from token
        $this->assertEquals($testUser->getEmail(), $user->getEmail());
        $this->assertEquals($testUser->getId(), $user->getId());
    }


    /**
      * @expectedException JobLion\AuthBundle\Exception
      */
    public function testChangedTokenWillBeRejected()
    {
        $testUser = $this->createTestUser();

        // generate login token for user
        $token = new Token($this->getConfigFile(), $this->getEntityManager());
        $tokenString = $token->generate($testUser);

        // change token
        $tokenString[4] = "%";

        // try to get user from token (should throw exception)
        $user = $token->getUser($tokenString);
    }

    /**
      * @expectedException JobLion\AuthBundle\Exception
      */
    public function testExpiredTokenWillGetRejected()
    {
        $testUser = $this->createTestUser();

        // generate login token that's only valid for 1 second
        $token = new Token($this->getConfigFile(), $this->getEntityManager());
        $tokenString = $token->generate($testUser, 1);

        // wait 1 second
        sleep(1);

        // try to get user from token (should throw exception)
        $user = $token->getUser($tokenString);
    }

    /**
      * @expectedException JobLion\AuthBundle\Exception
      */
    public function testTokenEncodedByWrongKeyWillGetRejected()
    {
        $testUser = $this->createTestUser();
        $key = openssl_random_pseudo_bytes(64);

        // create token as array
        $data = [
              'iat'  => time(),
              'nbf'  => time(),
              'exp'  => time() + 60,
              'data' => [
                  'userId'   => $testUser->getId()
              ]
          ];

        // encode token using the secret key
        $tokenString = JWT::encode($data, $key, 'HS512');

        // try to get user from alien token (should throw exception)
        $token = new Token($this->getConfigFile(), $this->getEntityManager());
        $user = $token->getUser($tokenString);
    }
}
