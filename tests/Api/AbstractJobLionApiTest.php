<?php namespace JobLion\Api;

use Silex\WebTestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use PDO;
use JobLion\Database\Backend\Migration\Migrator;
use JobLion\Database\Backend;
use JobLion\Database\Config;
use JobLion\Database\Account\User;

abstract class AbstractJobLionApiTest extends WebTestCase
{
    use TestCaseTrait;

    // only instantiate pdo once for test clean-up/fixture load
    private static $pdo = null;

    // only instantiate Database once per test
    private $conn = null;

    private $config;

    /**
     * create silex app
     */
    public function createApplication()
    {
        $this->config = new Config();
        $this->config->load();

        $app = App::init($this->getDb(), $this->config);
        $app['debug'] = true;
        $app['session.test'] = true;

        unset($app['exception_handler']);
        return $app;
    }

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                // load test database config
                $host = $this->config->get("testDbHost");
                $user = $this->config->get("testDbUser");
                $pw = $this->config->get("testDbPassword");
                $db = $this->config->get("testDbDatabase");
                $charset = 'utf8';

                // connect to test Database
                $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$pdo = new PDO($dsn, $user, $pw, $options);
            }

            $this->conn = $this->createDefaultDBConnection(self::$pdo, ':mysql:');
        }

        return $this->conn;
    }

    public function getDataSet()
    {
        return new \PHPUnit\DbUnit\DataSet\DefaultDataSet();
    }

    final public function getDb()
    {
        return new Backend\Fake($this->getConnection()->getConnection());
    }

    // migrate test database
    public function setUp()
    {
        parent::setup();

        // delete possible existing tables
        $this->dropTables();

        // upgrade to newest version
        $currentVersion = 0;
        $targetVersion = true;

        $migrator = new Migrator($currentVersion, $targetVersion, $this->getDb());

        // start migration, this should upgrade all versions
        $status = $migrator->migrateFolder();

        $this->init();
    }

    // remove all tables
    public function dropTables()
    {
        $sql = "SHOW TABLES";
        $tables = $this->getDb()->getPdo()->query($sql)->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $sql = "DROP TABLE `$table`";
            $this->getDb()->getPdo()->prepare($sql)->execute();
        }
    }

    protected function createTestUser($email="test@example.com", $password="abc123")
    {
        $user = new User($this->getDb());

        // additional information
        $firstName = "Test";
        $lastName = "Testus";

        // save user
        $user->setEmail($email)
             ->setFirstName($firstName)
             ->setLastName($lastName)
             ->setPassword($password)
             ->create();
    }

    protected function loginTestUser($email="test@example.com", $password="abc123")
    {
        // login user
        $client = $this->createClient();
        $crawler = $client->request(
             'POST',
             '/api/v1/user/login',
             array(
               "email" => $email,
               "password" => $password)
          );
    }


    public function init()
    {
    }
}
