<?php namespace JobLion\Database\Account;

use Hautelook\Phpass\PasswordHash;
use JobLion\Database\Exception;
use JobLion\Database\Entity\User;

/**
 * Static methods for hashing and checking passwords
 */
class Password
{
    /**
     * Check if the Password is correct for the given User
     *
     * @param  User $user        User entity to check password for
     * @param  string $password  Password to be checked
     * @return bool              true if the Password is correct, False otherwise
     */
    public static function check(User $user, string $password) : bool
    {
        $hasher = new PasswordHash(8, false);
        $check = $hasher->CheckPassword($password, $user->getHash());

        return $check;
    }

    /**
     * Hash Password string using Hautelook\Phpass
     *
     * @param  string $password Password to be hashed
     * @return string           Hashed Password
     */
    public static function hash(string $password) : string
    {
        $hasher = new PasswordHash(8, false);
        $hash = $hasher->HashPassword($password);
        if (strlen($hash) >= 20) {
            return $hash;
        } else {
            throw new Exception\Exception("Invalid Hash");
        }
    }
}
