<?php namespace JobLion\ExperienceReportBundle;

use JobLion\AbstractJobLionApiTest;
use JobLion\ExperienceReportBundle\Entity;

class ExperienceReportTest extends AbstractJobLionApiTest
{
    /**
     * Create Tests
     */

    public function testReportCanBeCreated()
    {
        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();

        // create test categories
        $categoryIds = [];
        for ($i = 0; $i < 3; $i++) {
            $category = $this->createTestJobCategory("Test Category $i");
            $categoryIds[] = $category->getId();
        }

        $title = "Test Report";
        $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                 Vestibulum eget ante viverra,
                 interdum ligula ac, maximus lectus. Aliquam non molestie nisl.
                 Fusce libero odio, porttitor in est sit amet, rutrum maximus
                 Etiam malesuada sem augue, id maximus augue tempus id. Maecenas
                 accumsan luctus. Donec egestas vel nulla et eleifend.
                 Proin id accumsan ex, sed dignissim magna. Nulla suscipit
                 a gravida odio. Sed condimentum, nulla at consequat vulputate,
                 Vivamus a est hendrerit, ultrices risus sit amet, luctus arcu. ";

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/experienceReports/',
          array(
            'jobCategoryIds' => $categoryIds,
            'title' => $title,
            'text' => $text,
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

        // check success answer
        $this->assertTrue($answer['success']);

        // check if report is in database
        $report = $this->getEntityManager()
                          ->find(Entity\ExperienceReport::class, 1);
        $this->assertTrue($report == true, "Report is not in Database");

        // check if values were saved correctly
        $this->assertEquals($title, $report->getTitle());
        $this->assertEquals($text, $report->getText());
        $this->assertEquals($user->getId(), $report->getUser()->getId());

        $jobCategories = $report->getJobCategories();
        $this->assertCount(3, $jobCategories, "Three Categories were added, so there should be 3 entries in the array");
        foreach ($categoryIds as $key => $categoryId) {
            $this->assertEquals($categoryId, $jobCategories[$key]->getId());
        }
    }

    public function testReportWithMissingValuesThrowsError()
    {
        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();
        // create test category
        $category = $this->createTestJobCategory();

        $title = "Test Report";
        $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.
               Vestibulum eget ante viverra,
               interdum ligula ac, maximus lectus. Aliquam non molestie nisl.
               Fusce libero odio, porttitor in est sit amet, rutrum maximus
               Etiam malesuada sem augue, id maximus augue tempus id. Maecenas
               accumsan luctus. Donec egestas vel nulla et eleifend.
               Proin id accumsan ex, sed dignissim magna. Nulla suscipit
               a gravida odio. Sed condimentum, nulla at consequat vulputate,
               Vivamus a est hendrerit, ultrices risus sit amet, luctus arcu. ";

        // send request without title
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/experienceReports/',
          array(
            'jobCategoryIds' => [$category->getId()],
            'text' => $text,
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

    public function testReportCanOnlyBeSubmittedWhenLoggedIn()
    {
        // create test user
        $user = $this->createTestUser();
        // create test category
        $category = $this->createTestJobCategory();

        $title = "Test Report";
        $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.
               Vestibulum eget ante viverra,
               interdum ligula ac, maximus lectus. Aliquam non molestie nisl.
               Fusce libero odio, porttitor in est sit amet, rutrum maximus
               Etiam malesuada sem augue, id maximus augue tempus id. Maecenas
               accumsan luctus. Donec egestas vel nulla et eleifend.
               Proin id accumsan ex, sed dignissim magna. Nulla suscipit
               a gravida odio. Sed condimentum, nulla at consequat vulputate,
               Vivamus a est hendrerit, ultrices risus sit amet, luctus arcu. ";

        // send request without logging in before
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/experienceReports/',
          array(
            "title" => $title,
            "jobCategoryIds" => [$category->getId()],
            "text" => $text
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

    public function testReportWithNonExistendJobCategoryThrowsError()
    {
        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();
        // create test category
        $category = $this->createTestJobCategory();

        $title = "Test Report";
        $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.
               Vestibulum eget ante viverra,
               interdum ligula ac, maximus lectus. Aliquam non molestie nisl.
               Fusce libero odio, porttitor in est sit amet, rutrum maximus
               Etiam malesuada sem augue, id maximus augue tempus id. Maecenas
               accumsan luctus. Donec egestas vel nulla et eleifend.
               Proin id accumsan ex, sed dignissim magna. Nulla suscipit
               a gravida odio. Sed condimentum, nulla at consequat vulputate,
               Vivamus a est hendrerit, ultrices risus sit amet, luctus arcu. ";

        // send request with an invalid job category id
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/experienceReports/',
          array(
            'title' => $title,
            'jobCategoryIds' => [1, 2],
            'text' => $text,
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
        $this->assertEquals("InvalidCategory", $answer['error'], "got wrong error");
    }

    public function testReportWithCommaSeperatedJobCategories()
    {
        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();

        // create test categories
        $categoryIds = [];
        for ($i = 0; $i < 2; $i++) {
            $category = $this->createTestJobCategory("Test Category $i");
            $categoryIds[] = $category->getId();
        }

        $title = "Test Report";
        $text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit.
               Vestibulum eget ante viverra,
               interdum ligula ac, maximus lectus. Aliquam non molestie nisl.
               Fusce libero odio, porttitor in est sit amet, rutrum maximus
               Etiam malesuada sem augue, id maximus augue tempus id. Maecenas
               accumsan luctus. Donec egestas vel nulla et eleifend.
               Proin id accumsan ex, sed dignissim magna. Nulla suscipit
               a gravida odio. Sed condimentum, nulla at consequat vulputate,
               Vivamus a est hendrerit, ultrices risus sit amet, luctus arcu. ";

        // send request with malformed job category ida
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          '/v1/experienceReports/',
          array(
            'title' => $title,
            'jobCategoryIds' => "1,2",
            'text' => $text,
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

        $report = $this->getEntityManager()
                          ->find(Entity\ExperienceReport::class, 1);
        $this->assertTrue($report == true, "Report is not in Database");

        // check if values were saved correctly
        $this->assertEquals($title, $report->getTitle());
        $this->assertEquals($text, $report->getText());
        $this->assertEquals($user->getId(), $report->getUser()->getId());

        $jobCategories = $report->getJobCategories();
        $this->assertCount(2, $jobCategories, "Two Categories were added, so there should be 2 entries in the array");
        foreach ($categoryIds as $key => $categoryId) {
            $this->assertEquals($categoryId, $jobCategories[$key]->getId());
        }
    }


    /**
     * List Tests
     */

    private function createTestReports()
    {
        // create test categories
        $this->createTestJobCategory("Test Category 1");
        $this->createTestJobCategory("Test Category 2");
        $this->createTestJobCategory("Test Category 3");

        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();

        // create three test reports
        for ($i = 1; $i <= 3; $i++) {
            $client = $this->createClient();
            $crawler = $client->request(
            'POST',
            '/v1/experienceReports/',
            array(
              'title' => "Report $i",
              'jobCategoryIds' => [$i],
              'text' => "Report text $i",
              'jwt' => $token
            )
          );
        }

        return $user;
    }

    public function testReportsCanBeListed()
    {
        $user = $this->createTestReports();

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
           'GET',
           '/v1/experienceReports/'
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
        $experienceReports = $answer;
        $this->assertCount(3, $experienceReports, "Three reports were created, so there should be 3 entries in the array");

        foreach ($experienceReports as $i => $report) {
            $this->assertEquals($i+1, $report['id'], "Id is not valid");
            $this->assertEquals("Report " . ($i+1), $report['title'], "Title is not valid");
            $this->assertEquals($user->toArray(), $report['user'], "User is not valid");
            $this->assertCount(1, $report['jobCategories'], "Each report should only have one category");
            $this->assertEquals($i+1, $report['jobCategories'][0]['id'], "Job category is not valid");
        }
    }

    /**
     * Get tests
     */

    public function testReportsCanBeReturned()
    {
        $user = $this->createTestReports();

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/v1/experienceReports/1'
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
        $report = $answer;

        $this->assertEquals(1, $report['id'], "Id is not valid");
        $this->assertEquals("Report 1", $report['title'], "Title is not valid");
        $this->assertEquals($user->toArray(), $report['user'], "User is not valid");
        $this->assertCount(1, $report['jobCategories'], "Each report should only have one category");
        $this->assertEquals(1, $report['jobCategories'][0]['id'], "Job category is not valid");
    }

    public function testReportWithWrongIdCanNotBeReturned()
    {
        $user = $this->createTestReports();

        // send request with invalid id
        $client = $this->createClient();
        $crawler = $client->request(
          'GET',
          '/v1/experienceReports/invalid'
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
