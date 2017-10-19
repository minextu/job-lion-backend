<?php namespace JobLion\Database\Backend;

use PDO;

/**
 * A Mysql PDO Connection
 */
class Mysql implements BackendInterface
{
    /**
     * charset to use
     *
     * @var string
     */

    private $charset = 'utf8';
    /**
     * PDO object
     *
     * @var PDO
     */
    private $pdo;

    public function __construct($host, $user, $pw, $db)
    {
        $dsn = "mysql:host=$host;dbname=$db;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ];
        $this->pdo = new PDO($dsn, $user, $pw, $options);
    }

    public function getPdo()
    {
        return $this->pdo;
    }
}
