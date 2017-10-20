<?php namespace JobLion\Database\Account;

use JobLion\Database\AbstractJobLionDatabaseTest;

class UserTest extends AbstractJobLionDatabaseTest
{
    public function testUserCanBeCreated()
    {
        // create test user
        $this->createTestUser("test@example.com");

        // check if user would be in Database
        $this->assertEquals(1, $this->getConnection()->getRowCount('users'), "Inserting failed");

        // check if values are saved correctly
        $queryTable = $this->getConnection()->createQueryTable('users', 'SELECT id,email,first_name,last_name FROM users');
        $expectedTable = $this->createFlatXmlDataSet(__DIR__."/UserTest.xml")->getTable("users");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testUserCanBeLoaded()
    {
        // create test user
        $this->createTestUser();

        $user = new User($this->getDb());

        $loadStatus = $user->loadEmail("test@example.com");
        $this->assertTrue($loadStatus);

        $email = $user->getEmail();
        $this->assertEquals("test@example.com", $email);
    }

    public function testUserCanBeLoadedById()
    {
        $this->createTestUser();

        // find user by email
        $user = new User($this->getDb());
        $loadStatus = $user->loadEmail("test@example.com");
        $this->assertTrue($loadStatus);
        $userId = $user->getId();

        // Load that user by id
        $user = new User($this->getDb(), $userId);
        $this->assertEquals($userId, $user->getId());

        $email = $user->getEmail();
        $this->assertEquals("test@example.com", $email);
    }

    /**
      * @expectedException JobLion\Database\Exception\InvalidId
      */
    public function testUserCanNotBeLoadedByInvalidId()
    {
        $this->createTestUser();

        // user with id -1 does not exist
        $user = new User($this->getDb(), -1);
    }

    /**
      * @expectedException JobLion\Database\Exception\Exception
      */
    public function testLoadedUserCanNotBeCreated()
    {
        $this->createTestUser();

        $user = new User($this->getDb());

        $loadStatus = $user->loadEmail("test@example.com");
        $this->assertTrue($loadStatus);

        $createStatus = $user->create();
    }

    /**
      * @expectedException JobLion\Database\Exception\Exception
      */
    public function testEmptyUserCanNotBeCreated()
    {
        $user = new User($this->getDb());

        $createStatus = $user->create();
    }

    /**
      * @expectedException JobLion\Database\Exception\Exception
      */
    public function testUserWithoutPasswordCanNotBeCreated()
    {
        $user = new User($this->getDb());
        $user->setEmail("test@example.com");
        $user->setFirstName("Test");
        $user->setLastName("Testus");

        $createStatus = $user->create();
    }

    /**
      * @expectedException JobLion\Database\Exception\Exception
      */
    public function testUserWithoutRealNameCanNotBeCreated()
    {
        $user = new User($this->getDb());
        $user->setEmail("test@example.com");
        $user->setFirstName("Test");
        $user->setPassword("abc123");

        $createStatus = $user->create();
    }

    /*
     * E-Mail Checks
    */

    /**
      * @expectedException JobLion\Database\Exception\EmailExists
      */
    public function testAlreadyExistingEmail()
    {
        $this->createTestUser();

        $user = new User($this->getDb());
        $nickStatus = $user->setEmail("test@example.com");
    }

    /**
      * @expectedException JobLion\Database\Exception\InvalidEmail
      */
    public function testEmptyEmail()
    {
        $user = new User($this->getDb());
        $nickStatus = $user->setEmail("");
    }

    /**
      * @expectedException JobLion\Database\Exception\InvalidEmail
      */
    public function testInvalidEmail()
    {
        $user = new User($this->getDb());
        $nickStatus = $user->setEmail("notAnEmailAddress");
    }

    /*
     * Password Checks
     */

    public function testValidPasswordCheck()
    {
        $this->createTestUser();

        $user = new User($this->getDb());
        $loadStatus = $user->loadEmail("test@example.com");

        $password = "abc123";

        $validPassword = $user->checkPassword($password);
        $this->assertTrue($validPassword);
    }

    public function testInvalidPasswordCheck()
    {
        $this->createTestUser();

        $user = new User($this->getDb());
        $loadStatus = $user->loadEmail("test@example.com");

        $password = "wrong password";

        $validPassword = $user->checkPassword($password);
        $this->assertFalse($validPassword);
    }

    public function testEmptyPasswordCheck()
    {
        $this->createTestUser();

        $user = new User($this->getDb());
        $loadStatus = $user->loadEmail("test@example.com");

        $password = "";

        $validPassword = $user->checkPassword($password);
        $this->assertFalse($validPassword);
    }

    /**
      * @expectedException JobLion\Database\Exception\InvalidPassword
      */
    public function testInvalidShortPassword()
    {
        // A Password shorter than 6 characters should be invalid
        $user = new User($this->getDb());
        $passwordStatus = $user->setPassword("abc12");
    }
}
