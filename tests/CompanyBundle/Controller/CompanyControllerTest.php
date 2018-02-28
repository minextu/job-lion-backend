<?php namespace JobLion\CompanyBundle;

use JobLion\AbstractJobLionApiTest;
use JobLion\CompanyBundle\Entity;

class CompanyControllerTest extends AbstractJobLionApiTest
{
    /**
     * Create Tests
     */

    public function testCompanyCanBeCreated()
    {
        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();

        $title = "Test Company";

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/companies/',
          array(
            'title' => $title,
            'jwt' => $token
          )
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

        // get the id
        $id = $answer['id'];

        // check if company is in database
        $company = $this->getEntityManager()
                          ->find(Entity\Company::class, $id);
        $this->assertTrue($company == true, "Company is not in Database");

        // check if values were saved correctly
        $this->assertEquals($title, $company->getTitle());
        $this->assertEquals($user->getId(), $company->getUser()->getId());
    }

    public function testCompanyWithMissingValuesThrowsError()
    {
        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();

        // send request without title
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/companies/',
          array(
            'jwt' => $token
          )
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

    public function testCompanyCanOnlyBeSubmittedWhenLoggedIn()
    {
        // create test user
        $user = $this->createTestUser();

        $title = "Test Company";

        // send request without logging in before
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/companies/',
          array(
            "title" => $title
          )
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
     * List Tests
     */

    private function createTestCompanies()
    {
        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();

        // create three test companies
        for ($i = 1; $i <= 3; $i++) {
            $client = $this->createClient();
            $crawler = $client->request(
              'POST',
              '/v1/companies/',
              array(
                'title' => "Company $i",
                'jwt' => $token
              )
            );
        }

        return $user;
    }

    public function testCompaniesCanBeListed()
    {
        $user = $this->createTestCompanies();

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
           'GET',
           '/v1/companies/'
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

        // check response
        $companies = $answer['companies'];
        $this->assertCount(3, $companies, "Three companies were created, so there should be 3 entries in the array");
        $this->assertEquals(3, $answer['total'], "Three companies were created, so total should also be 3");

        foreach ($companies as $i => $company) {
            $this->assertEquals($i+1, $company['id'], "Id is not valid");
            $this->assertEquals("Company " . ($i+1), $company['title'], "Title is not valid");
            $this->assertEquals($user->toArray(), $company['user'], "User is not valid");
        }
    }

    public function testReportsCanBeListedWithParameters()
    {
        $user = $this->createTestCompanies();

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
           'GET',
           '/v1/companies/',
           array(
             'limit' => 1,
             'offset' => 1
           )
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

        // check response
        $companies = $answer['companies'];
        $this->assertCount(1, $companies, "Only one company matches the criteria, so there should be one entry in the array");
        $this->assertEquals(3, $answer['total'], "Three companies were created, so total should be 3");

        $company = $companies[0];
        $this->assertEquals(2, $company['id'], "Id is not valid");
        $this->assertEquals("Company 2", $company['title'], "Title is not valid");
        $this->assertEquals($user->toArray(), $company['user'], "User is not valid");
    }

    /**
     * Get tests
     */

    public function testCompanyCanBeReturned()
    {
        $user = $this->createTestCompanies();

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/v1/companies/1'
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

        // check response
        $company = $answer;

        $this->assertEquals(1, $company['id'], "Id is not valid");
        $this->assertEquals("Company 1", $company['title'], "Title is not valid");
        $this->assertEquals($user->toArray(), $company['user'], "User is not valid");
    }

    public function testCompanyWithWrongIdCanNotBeReturned()
    {
        $user = $this->createTestCompanies();

        // send request with invalid id
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/companies/invalid'
        );

        // decode answer
        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);
        $error = isset($answer['error']) ? $answer['error'] : false;
        $errorMessage = isset($answer['message']) ? $answer['message'] : false;

        // check return code
        $this->assertEquals(
          404,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("NotFound", $answer['error'], "got wrong error");
    }
}
