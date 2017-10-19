<?php namespace JobLion\JobLion\Account;

use JobLion\JobLion\Exception\Exception;
use JobLion\JobLion\Database\DatabaseInterface;
use JobLion\JobLion\Account\User;

/**
 * Static class used to login and logout using the session cookie
 */
class Account
{
    /**
     * Check if the current user is logged in
     *
     * @param  DatabaseInterface $db Main database
     * @return bool|User             Matching user object when logged in, false otherwise
     */
    public static function checkLogin(DatabaseInterface $db)
    {
        if (!isset($_SESSION['ettc']['userId'])) {
            $user = false;
        } else {
            try {
                $user = new User($db, $_SESSION['ettc']['userId']);
            } catch (Exception $e) {
                self::logout();
                $user = false;
            }
        }

        return $user;
    }

    /**
     * Sets the users session to logged in
     *
     * @param User $user The user that was logged in
     */
    public static function login(User $user)
    {
        $_SESSION['ettc']['userId'] = $user->getId();
    }

    /**
     * Sets the users session to be logged out
     */
    public static function logout()
    {
        unset($_SESSION['ettc']['userId']);
    }
}
