<?php namespace JobLion\JobLion\Database;

use JobLion\JobLion\Exception;
use PDO;

/**
 * Dummy Database, used for Testing
 */
class Fake implements DatabaseInterface
{
    /**
     * PDO object
     *
     * @var PDO
     */
    private $pdo;

    public function __construct($pdo, $user="", $pw="", $db="")
    {
        $this->pdo = $pdo;
    }

    public function getPdo()
    {
        return $this->pdo;
    }
}
