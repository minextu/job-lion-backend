<?php namespace JobLion\AuthBundle;

use JobLion\AbstractJobLionApiTest;
use JobLion\AuthBundle\Password;
use JobLion\AppBundle\Entity\User;

class AuthTest extends AbstractJobLionApiTest
{
    /**
     * Create User Tests
    **/

    public function testUserCanBeCreated()
    {
        $email = "test@example.com";
        $firstName = "Test";
        $lastName = "Testus";
        $password = "abc123";

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
            'POST',
            '/v1/auth/register',
            array(
              "email" => $email,
              "firstName" => $firstName,
              "lastName" => $lastName,
              "password" => $password)
        );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
            201,
            $client->getResponse()->getStatusCode(),
            "error: $error, message: $errorMessage"
        );

        // get id
        $id = $answer['id'];

        // check if user is in database
        $user = $this->getEntityManager()
                        ->getRepository(User::class)
                        ->findOneBy(array('email' => $email));
        $this->assertTrue($user == true, "User is not in Database");

        // check if values were saved correctly
        $this->assertEquals($id, $user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($lastName, $user->getLastName());
        $this->assertTrue(Password::check($user, $password));
    }

    public function testCreateWithMissingValuesThrowsError()
    {
        // send request, do not send a password
        $client = $this->createClient();
        $crawler = $client->request(
            'POST',
            '/v1/auth/register',
            array(
            "email" => "test@example.com",
            "firstName" => "Test",
            "lastName" => "Testus")
        );

        // Return code should be an error
        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        // check error text
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $this->assertEquals("MissingValues", $answer['error'], "got wrong error");
    }

    public function testCreateWithExistingEmailThrowsError()
    {
        $this->createTestUser("test@example.com");

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
            'POST',
            '/v1/auth/register',
            array(
            "email" => "test@example.com",
            "firstName" => "Test",
            "lastName" => "Testus",
            "password" => "abc123")
        );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // Return code should be an error
        $this->assertEquals(
            409,
            $client->getResponse()->getStatusCode(),
            "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("EmailExists", $error, "got wrong error");
    }

    public function testCreateWithInvalidEmailThrowsError()
    {
        // send request with invalid Email
        $client = $this->createClient();
        $crawler = $client->request(
            'POST',
            '/v1/auth/register',
            array(
            "email" => "example.com",
            "firstName" => "Test",
            "lastName" => "Testus",
            "password" => "abc123")
        );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // Return code should be an error
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode(),
            "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("InvalidEmail", $answer['error'], "got wrong error");
    }

    public function testCreateWithShortPasswordThrowsError()
    {
        // send request with a too short password
        $client = $this->createClient();
        $crawler = $client->request(
            'POST',
            '/v1/auth/register',
            array(
            "email" => "test@example.com",
            "firstName" => "Test",
            "lastName" => "Testus",
            "password" => "abc12")
        );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // Return code should be an error
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode(),
            "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("InvalidPassword", $answer['error'], "got wrong error");
    }

    /**
     * Login User Tests
    **/

    public function testUserCanBeLoggedIn()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        $user = $this->createTestUser($email, $password);

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/auth/login',
          array(
            "email" => $email,
            "password" => $password)
      );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
          200,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check if token exists
        $this->assertNotEmpty($answer['token']);

        // validate user
        $this->assertNotEmpty($answer['user']);
        $userReturn = $answer['user'];
        $this->assertEquals($user->getId(), $userReturn['id']);
        $this->assertEquals($user->getAvatar(), $userReturn['avatar']);
        $this->assertEquals($user->getFirstName(), $userReturn['firstName']);
        $this->assertEquals($user->getLastName(), $userReturn['lastName']);
        $this->assertFalse($userReturn['isAdmin']);
    }

    public function testUserCanNotBeLoggedInWhenNotActivated()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        // set activated to false
        $this->createTestUser($email, $password, false);

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/auth/login',
          array(
            "email" => $email,
            "password" => $password)
      );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
          401,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("NotActivated", $answer['error'], "got wrong error");
    }

    public function testUserCanBeNotBeLoggedInWithWrongPassword()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);

        // send request with wrong password
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/auth/login',
          array(
            "email" => $email,
            "password" => "wrongPassword")
      );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
          401,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("InvalidLogin", $answer['error'], "got wrong error");
    }

    public function testUserCanBeNotBeLoggedInWithWrongEmail()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);

        // send request with wrong email
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/auth/login',
          array(
            "email" => "wrongEmail@example.com",
            "password" => $password)
      );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
          401,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("InvalidLogin", $answer['error'], "got wrong error");
    }

    public function testUserCanBeNotBeLoggedInWithMissingPassword()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);

        // send request without a password
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/auth/login',
          array(
            "email" => $email)
        );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
          400,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("MissingValues", $answer['error'], "got wrong error");
    }

    /**
     * Login info tests
     */
    public function testLoginUserInfoCanBeParsed()
    {
        // create test user
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);
        $token = $this->loginTestUser($email, $password);

        // send request to get info
        $client = $this->createClient();
        $crawler = $client->request(
           'GET',
           '/v1/auth/info',
           array('jwt' => $token)
        );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
           200,
           $client->getResponse()->getStatusCode(),
           "error: $error, message: $errorMessage"
        );

        // check data
        $this->assertArrayHasKey("user", $answer, "User wasn't returned");
        $user = $answer['user'];
        $this->assertEquals(1, $user['id']);
        $this->assertEquals("Test", $user['firstName']);
        $this->assertEquals("Testus", $user['lastName']);
        $this->assertFalse($user['isAdmin']);
    }

    public function testLoginUserInfoOfAdminUser()
    {
        // create test user
        $email = "test@example.com";
        $password = "abc123";

        // create admin user
        $this->createTestUser($email, $password, true, true);
        $token = $this->loginTestUser($email, $password);

        // send request to get info
        $client = $this->createClient();
        $crawler = $client->request(
           'GET',
           '/v1/auth/info',
           array('jwt' => $token)
        );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
           200,
           $client->getResponse()->getStatusCode(),
           "error: $error, message: $errorMessage"
        );

        // check data
        $this->assertArrayHasKey("user", $answer, "User wasn't returned");
        $user = $answer['user'];
        $this->assertEquals(1, $user['id']);
        $this->assertEquals("Test", $user['firstName']);
        $this->assertEquals("Testus", $user['lastName']);
        $this->assertTrue($user['isAdmin']);
    }

    public function testLoginUserInfoThrowsErrorWhenNoLoginTokenIsProvided()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);

        // send request without logging in before
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/auth/info'
        );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
          401,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("NotLoggedIn", $answer['error'], "got wrong error");
    }

    /**
     * Activate tests
     */
    public function testUserCanBeActivated()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        // set activated to false
        $user = $this->createTestUser($email, $password, false);
        ConfirmationMail::accountActivation($user, true);

        // send request to activate user
        $code = $user->getActivationCode();
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/auth/activate',
          array(
            "user" => $user->getId(),
            "activationCode" => $code)
      );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "error: $error, message: $errorMessage"
        );

        // check if user is activated
        $this->assertTrue($user->getActivated());
        $this->assertEmpty($user->getActivationCode(), "Activation Code should be removed");
    }

    public function testUserCanNotBeActivatedWithWrongCode()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        // set activated to false
        $user = $this->createTestUser($email, $password, false);
        ConfirmationMail::accountActivation($user, true);

        // send request to activate user
        $code = "wrong";
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/auth/activate',
          array(
            "user" => $user->getId(),
            "activationCode" => $code)
      );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
          400,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("InvalidActivationCode", $answer['error'], "got wrong error");
    }

    public function testUserCanNotBeActivatedWithWrongUserId()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        // set activated to false
        $user = $this->createTestUser($email, $password, false);
        ConfirmationMail::accountActivation($user, true);

        // send request to activate user
        $code = $user->getActivationCode();
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/auth/activate',
          array(
            "user" => 9999,
            "activationCode" => $code)
      );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
          400,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("InvalidActivationCode", $answer['error'], "got wrong error");
    }
}
