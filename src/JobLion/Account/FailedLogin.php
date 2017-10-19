<?php namespace JobLion\JobLion\Account;

use JobLion\JobLion\Database\DatabaseInterface;

/**
 * Static class used log and get failed logins
 */
class FailedLogin
{
    /**
     * Add a failed login attempt, will also log ip address
     *
     * @param DatabaseInterface $db    Database to be sued
     * @param string            $email E-Mail to log
     */
    public static function add(DatabaseInterface $db, $email)
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        $sql = 'INSERT into failedLogins
                (email, ip)
                VALUES (?, ?)';
        $stmt = $db->getPdo()->prepare($sql);
        $status = $stmt->execute([$email, $ip]);

        return $status;
    }

    /**
     * Try to fetch the time of last login attempt
     *
     * @param  DatabaseInterface $db    Database to be sued
     * @param  string            $email E-Mail that was logged
     * @return Mysql time of last login attempt, or False if non exist
     */
    public static function getLastTime(DatabaseInterface $db, $email)
    {
        $sql = 'SELECT `time` FROM failedLogins WHERE email=? ORDER BY `time` DESC LIMIT 1';

        $stmt = $db->getPdo()->prepare($sql);
        $stmt->execute([$email]);

        $time = $stmt->fetchColumn();
        return $time;
    }
}
