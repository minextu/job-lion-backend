<?php namespace JobLion\Database\Backend;

use JobLion\JobLion\Exception;
use PDO;

/**
 * Dummy Database backend, used for Testing
 */
class Fake implements BackendInterface
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
