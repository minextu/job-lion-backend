<?php namespace JobLion\Api;

use JobLion\Api\AbstractJobLionApiTest;
use JobLion\Database\Account\User;

class UserTest extends AbstractJobLionApiTest
{
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
            '/api/v1/user/create',
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
        $user = new User($this->getDb());
        $loadStatus = $user->loadEmail($email);
        $this->assertTrue($loadStatus, "User is not in Database");

        // check if values were saved correctly
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($lastName, $user->getLastName());
        $this->assertTrue($user->checkPassword($password));
    }

    public function testMissingValuesThrowsError()
    {
        // send request, do not send a password
        $client = $this->createClient();
        $crawler = $client->request(
            'POST',
            '/api/v1/user/create',
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
        $this->assertEquals("MissingValues", $answer['error'], "error missing");
    }

    public function testExistingEmailThrowsError()
    {
        $this->createTestUser("test@example.com");

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
            'POST',
            '/api/v1/user/create',
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
        $this->assertEquals("EmailExists", $error, "error missing");
    }

    public function testInvalidEmailThrowsError()
    {
        // send request with invalid Email
        $client = $this->createClient();
        $crawler = $client->request(
            'POST',
            '/api/v1/user/create',
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
        $this->assertEquals("InvalidEmail", $answer['error'], "error missing");
    }
}
