<?php namespace JobLion\CommentBundle;

use JobLion\AbstractJobLionApiTest;
use JobLion\CommentBundle\Entity;

class CommentControllerTest extends AbstractJobLionApiTest
{
    /**
     * Create Tests
     */

    public function testCommentCanBeCreated()
    {
        // create a test report and login user
        $report = $this->createTestReport();
        $user = $report->getUser();
        $token = $this->loginTestUser();

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
          "/v1/experienceReports/" . $report->getId() . "/comments",
          array(
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

        // get id
        $id = $answer['id'];

        // check if comment is in database
        $comment = $this->getEntityManager()
                          ->find(Entity\Comment::class, 1);
        $this->assertTrue($comment == true, "Comment is not in Database");

        // check if values were saved correctly
        $this->assertEquals($text, $comment->getText());
        $this->assertEquals($user->getId(), $comment->getUser()->getId());
        $this->assertEquals($id, $comment->getExperienceReport()->getId());
    }

    public function testCommentCanNotBeCreatedWithoutText()
    {
        // create a test report and login user
        $report = $this->createTestReport();
        $user = $report->getUser();
        $token = $this->loginTestUser();

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          "/v1/experienceReports/" . $report->getId() . "/comments",
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

    public function testCommentCanNotBeCreatedWithInvalidReportId()
    {
        // create a test report and login user
        $report = $this->createTestReport();
        $user = $report->getUser();
        $token = $this->loginTestUser();

        $text = "Test";

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          "/v1/experienceReports/999/comments",
          array(
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
        $this->assertEquals("InvalidId", $answer['error'], "got wrong error");
    }

    public function testCommentCanNotBeCreatedWhenNotLoggedIn()
    {
        // create a test report and do not login
        $report = $this->createTestReport();
        $user = $report->getUser();

        $text = "Test";

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
          'POST',
          "/v1/experienceReports/" . $report->getId() . "/comments",
          array(
            'text' => $text
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
     * List tests
     */

    private function createTestComments()
    {
        // create test report
        $report = $this->createTestReport("Test Report 1");

        // login test user
        $user = $report->getUser();
        $token = $this->loginTestUser();

        // create three test comments
        for ($i = 1; $i <= 3; $i++) {
            $client = $this->createClient();
            $crawler = $client->request(
               'POST',
               "/v1/experienceReports/" . $report->getId() . "/comments",
               array(
                 'text' => "Comment $i",
                 'jwt' => $token
               )
             );
        }

        return $report;
    }

    public function testCommentsCanBeListed()
    {
        $report = $this->createTestComments();
        $user = $report->getUser();

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/v1/experienceReports/' . $report->getId() . '/comments'
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
        $comments = $answer;
        $this->assertCount(3, $comments, "Three comments were created, so there should be 3 entries in the array");

        foreach ($comments as $i => $comment) {
            $this->assertEquals($i+1, $comment['id'], "Id is not valid");
            $this->assertEquals("Comment " . ($i+1), $comment['text'], "Text is not valid");
            $this->assertEquals($user->toArray(), $comment['user'], "User is not valid");
        }
    }

    public function testCommentsCanBeListedWithParameters()
    {
        $report = $this->createTestComments();
        $user = $report->getUser();

        // send request
        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/v1/experienceReports/' . $report->getId() . '/comments',
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
        $comments = $answer;
        $this->assertCount(1, $comments, "Only one comment matches the criteria, so there should be one entry in the array");

        $comment = $comments[0];
        $this->assertEquals(2, $comment['id'], "Id is not valid");
        $this->assertEquals("Comment 2", $comment['text'], "Text is not valid");
        $this->assertEquals($user->toArray(), $comment['user'], "User is not valid");
    }
}
