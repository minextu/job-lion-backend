<?php namespace JobLion\AppBundle;

use JobLion\AbstractJobLionApiTest;
use JobLion\AppBundle\Entity;

class JobCategoryTest extends AbstractJobLionApiTest
{
    /**
     * Create Tests
     */

    public function testJobCategoryCanBeCreated()
    {
        // create and login test user
        $this->createTestUser();
        $token = $this->loginTestUser();

        $name = "Test Category";

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/jobCategories/',
          array(
            'name' => $name,
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

        // get id
        $id = $answer['id'];

        // check if job category is in database
        $jobCategory = $this->getEntityManager()
                              ->getRepository(Entity\JobCategory::class)
                              ->findOneBy(array('name' => $name));
        $this->assertTrue($jobCategory == true, "Job Category is not in Database");

        // check if values were saved correctly
        $this->assertEquals($name, $jobCategory->getName());
        $this->assertEquals($id, $jobCategory->getUser()->getId());
    }

    public function testJobCategoryCanBeCreatedUsingJson()
    {
        // create and login test user
        $this->createTestUser();
        $token = $this->loginTestUser();

        $name = "Test Category";

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/jobCategories/',
          array(),
          array(),
          array('CONTENT_TYPE' => 'application/json'),
          json_encode([
            'name' => $name,
            'jwt' => $token
          ])
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

        // check if job category is in database
        $jobCategory = $this->getEntityManager()
                              ->getRepository(Entity\JobCategory::class)
                              ->findOneBy(array('name' => $name));
        $this->assertTrue($jobCategory == true, "Job Category is not in Database");

        // check if values were saved correctly
        $this->assertEquals($name, $jobCategory->getName());
        $this->assertEquals($id, $jobCategory->getUser()->getId());
    }

    public function testJobCategoryWithMissingValuesThrowsError()
    {
        // create and login test user
        $this->createTestUser();
        $token = $this->loginTestUser();

        $name = "Test Category";

        // send request without sending a name
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/jobCategories/',
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

    public function testJobCategoryCanNotBeCreatedWhenNotLoggedIn()
    {
        $this->createTestUser();

        $name = "Test Category";

        // send request without logging in before
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/jobCategories/',
          array(
            "name" => $name
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

    public function testJobCategoryCanNotBeCreatedTwice()
    {
        $this->createTestUser();
        $token = $this->loginTestUser();

        $name = "Test Category";

        // create category
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/jobCategories/',
          array(
            'name' => $name,
            'jwt' => $token
          )
        );

        // create category again
        $crawler = $client->request(
          'POST',
          '/v1/jobCategories/',
          array(
            'name' => $name,
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
          409,
          $client->getResponse()->getStatusCode(),
          "error: $error, message: $errorMessage"
        );

        // check error text
        $this->assertEquals("CategoryExists", $answer['error'], "got wrong error");
    }

    /**
     * List Tests
     */

    public function testJobCategoriesCanBeListed()
    {
        // create test categories
        $this->createTestJobCategory("Test Category 1");
        $this->createTestJobCategory("Test Category 2");
        $this->createTestJobCategory("Test Category 3");

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/jobCategories/'
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
        $jobCategories = $answer;
        $this->assertCount(3, $jobCategories, "Three Categories were created, so there should be 3 entries in the array");

        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals(
                $i+1,
                $jobCategories[$i]['id'],
                "Id is not valid"
            );

            $this->assertEquals(
                "Test Category " . ($i+1),
                $jobCategories[$i]['name'],
                "Name was not returned correctly"
            );
        }
    }


    /**
     * Get Tests
     */

    public function testJobCategoryCanBeReturned()
    {
        // create test categories
        $this->createTestJobCategory("Test Category 1");
        $this->createTestJobCategory("Test Category 2");
        $this->createTestJobCategory("Test Category 3");

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/jobCategories/1'
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
        $jobCategory = $answer;


        $this->assertEquals(
          1,
          $jobCategory['id'],
          "Id is not valid"
        );

        $this->assertEquals(
          "Test Category 1",
          $jobCategory['name'],
          "Name was not returned correctly"
        );
    }

    public function testJobCategoryWithWrongIdCanNotBeReturned()
    {
        // create test categories
        $this->createTestJobCategory("Test Category 1");
        $this->createTestJobCategory("Test Category 2");
        $this->createTestJobCategory("Test Category 3");

        // send request with invalid id
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/jobCategories/invalid'
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

    /**
     * Delete tests
     */
    public function testCategoryCanBeDeleted()
    {
        // create and login test user
        $user = $this->createTestUser("test@example.com", "abc123", true, true);
        $token = $this->loginTestUser();

        $this->createTestJobCategory("Test Category 1");

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
             'DELETE',
             '/v1/jobCategories/1',
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
             200,
             $client->getResponse()->getStatusCode(),
             "error: $error, message: $errorMessage"
           );

        // check if category got deleted
        $category = $this->getEntityManager()
                          ->find(Entity\JobCategory::class, 1);
        $this->assertTrue($category == false, "Category is still in Database");
    }

    public function testCategoryCanNotBeDeletedWhenLoggedOut()
    {
        $this->createTestJobCategory("Test Category 1");

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
             'DELETE',
             '/v1/jobCategories/1'
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

    public function testCategoryCanNotBeDeletedAsNonAdmin()
    {
        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();

        $this->createTestJobCategory("Test Category 1");

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
             'DELETE',
             '/v1/jobCategories/1',
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
             401,
             $client->getResponse()->getStatusCode(),
             "error: $error, message: $errorMessage"
        );
        // check error text
        $this->assertEquals("NoPermissions", $answer['error'], "got wrong error");
    }
}
