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
            200,
            $client->getResponse()->getStatusCode(),
            "error: $error, message: $errorMessage"
        );

        // check success answer
        $this->assertTrue($answer['success']);

        // check if user is in database
        $user = $this->getEntityManager()
                        ->getRepository(User::class)
                        ->findOneBy(array('email' => $email));
        $this->assertTrue($user == true, "User is not in Database");

        // check if values were saved correctly
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

        $this->createTestUser($email, $password);

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/user/login',
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

        // check success answer
        $this->assertTrue($answer['success']);
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
          '/v1/user/login',
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
          '/v1/user/login',
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
          '/v1/user/login',
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

    public function testUserCanBeNotBeLoggedInTwice()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);

        // login user
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/user/login',
          array(
            "email" => $email,
            "password" => $password)
          );

        // try to login again
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/user/login',
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
          409,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("AlreadyLoggedIn", $answer['error'], "got wrong error");
    }

    /**
     * Logout Tests
     */

    public function testUserCanBeLoggedOut()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);
        $this->loginTestUser($email, $password);

        // send logout request
        $client = $this->createClient();
        $crawler = $client->request(
           'POST',
           '/v1/user/logout'
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
    }

    public function testUserCanBeNotBeLoggedOutAgain()
    {
        // create test user
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);

        // send request to logout (without login before)
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/user/logout'
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
     * Login info tests
     */
    public function testLoginUserInfoCanBeParsed()
    {
        // create test user
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);
        $this->loginTestUser($email, $password);

        // send request to get info
        $client = $this->createClient();
        $crawler = $client->request(
           'GET',
           '/v1/user/info'
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
        $this->assertEquals($email, $user['email']);
        $this->assertEquals("Test", $user['firstName']);
        $this->assertEquals("Testus", $user['lastName']);
    }

    public function testLoginUserInfoThrowsErrorWhenNotLoggedIn()
    {
        // create test user, to login with
        $email = "test@example.com";
        $password = "abc123";

        $this->createTestUser($email, $password);

        // send request without logging in before
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/user/info'
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
}
