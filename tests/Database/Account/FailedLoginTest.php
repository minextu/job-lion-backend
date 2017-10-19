<?php namespace JobLion\Database\Account;

use JobLion\Database\AbstractJobLionDatabaseTest;

class FailedLoginTest extends AbstractJobLionDatabaseTest
{
    public function testFailedLoginCanBeCreated()
    {
        $email = "test@example.com";
        $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

        $status = FailedLogin::add($this->getDb(), $email);
        $this->assertTrue($status, "add failed");

        // check if value got saved
        $queryTable = $this->getConnection()->createQueryTable('failedLogins', 'SELECT id,email,ip FROM failedLogins');
        $expectedTable = $this->createFlatXmlDataSet(__DIR__."/FailedLoginTest.xml")->getTable("failedLogins");
        $this->assertTablesEqual($expectedTable, $queryTable);

        // check if failed login can be loaded
        $lastLoginAttempt = FailedLogin::getLastTime($this->getDb(), $email);
        $this->assertEquals(time(), strtotime($lastLoginAttempt), "Time of last login does not match", 5);
    }

    public function testLastLoginForUnloggedUser()
    {
        $email = "test2@example.com";

        // check if failed login won't get loaded
        $lastLoginAttempt = FailedLogin::getLastTime($this->getDb(), $email);
        $this->assertFalse($lastLoginAttempt);
    }

    public function testOnMultibleEntriesTheNewestOneWillGetReturned()
    {
        $email = "test@example.com";
        $_SERVER['REMOTE_ADDR'] = "127.0.0.1";

        // add two failed logins with a delay of 2 seconds
        FailedLogin::add($this->getDb(), $email);
        sleep(2);
        FailedLogin::add($this->getDb(), $email);

        // check if newest entry will get returned
        $lastLoginAttempt = FailedLogin::getLastTime($this->getDb(), $email);
        $this->assertEquals(time(), strtotime($lastLoginAttempt), "Time of last login does not match", 5);
    }
}
