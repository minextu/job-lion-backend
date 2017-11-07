<?php namespace JobLion\AuthBundle;

use JobLion\AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    public function testPasswordCanBeHashed()
    {
        $password = "abc123";
        $hash = Password::hash($password);

        $this->assertNotEmpty($hash);
        $this->assertGreaterThanOrEqual(20, strlen($hash), "Hash must be greater or equal then 20 characters");
        $this->assertLessThanOrEqual(100, strlen($hash), "Hash must be less or equal than 100 characters");
    }

    public function testHashCanBeChecked()
    {
        $password = "abc123";

        $user = new User();
        $user->setHash(Password::hash($password));

        $check = Password::check($user, $password);
        $this->assertTrue($check);
    }

    public function testWrongPassword()
    {
        $password = "abc123";

        $user = new User();
        $user->setHash(Password::hash($password));

        $check = Password::check($user, "wrong password");
        $this->assertFalse($check);
    }
}
