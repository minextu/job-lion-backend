<?php namespace JobLion\Api;

use Silex\WebTestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use PDO;
use JobLion\Database\EntityManager;
use JobLion\Database\ConfigFile;
use JobLion\Database\Entity;
use JobLion\Database\Account\Password;
use Doctrine\ORM\Tools\SchemaTool;

abstract class AbstractJobLionApiTest extends WebTestCase
{
    use TestCaseTrait;

    // only instantiate db for test clean-up/fixture load
    private static $pdo = null;
    private static $conn = null;
    private static $entityManager = null;
    private static $configFile = null;

    /**
     * create silex app
     */
    public function createApplication()
    {
        $app = App::init($this->getEntityManager(), self::$configFile);
        $app['debug'] = true;
        $app['session.test'] = true;

        unset($app['exception_handler']);
        return $app;
    }

    /**
     * Init database connection
     */
    final private function getConnection()
    {
        if (self::$conn === null) {
            self::$configFile = new ConfigFile();
            self::$configFile->load();

            self::$entityManager = EntityManager::create(self::$configFile, true);
            self::$pdo = self::$entityManager->getConnection()->getWrappedConnection();
            self::$conn = $this->createDefaultDBConnection(self::$pdo, ':mysql:');
        }

        return self::$conn;
    }

    /**
     * Dataset will be managed by doctrine
     */
    private function getDataSet()
    {
        return new \PHPUnit\DbUnit\DataSet\DefaultDataSet();
    }

    /**
     * Get doctrine entity manager
     * @return \Doctrine\ORM\EntityManager Doctrine Entity Manager
     */
    final protected function getEntityManager()
    {
        if (self::$entityManager === null) {
            $this->getConnection();
        }

        return self::$entityManager;
    }

    /**
     * Migrate database using doctrine
     */
    public function setUp()
    {
        parent::setup();

        // delete possible existing tables
        $this->dropTables();

        // init database schema
        $schemaTool = new SchemaTool($this->getEntityManager());
        $schemaTool->createSchema($this->getEntityManager()->getMetadataFactory()->getAllMetadata());
    }

    /**
     * Remove all tables in test database
     */
    private function dropTables()
    {
        $sql = "SHOW TABLES";
        $tables = self::$pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $sql = "DROP TABLE `$table`";
            self::$pdo->prepare($sql)->execute();
        }
    }

    /**
     * Create a test user
     * @param  string $email
     * @param  string $password
     */
    protected function createTestUser($email="test@example.com", $password="abc123")
    {
        $user = new Entity\User();

        // additional information
        $firstName = "Test";
        $lastName = "Testus";

        // save user
        $user->setEmail($email)
             ->setFirstName($firstName)
             ->setLastName($lastName)
             ->setHash(Password::hash($password));

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Login the given user
     * @param  string $email
     * @param  string $password
     */
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
}
