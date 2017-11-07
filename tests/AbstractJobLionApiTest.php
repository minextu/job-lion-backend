<?php namespace JobLion;

use Silex\WebTestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use PDO;
use JobLion\AppBundle\AppBundle;
use JobLion\AppBundle\EntityManager;
use JobLion\AppBundle\ConfigFile;
use JobLion\AppBundle\Entity;
use JobLion\AuthBundle\Password;
use Doctrine\ORM\Tools\SchemaTool;

abstract class AbstractJobLionApiTest extends WebTestCase
{
    use TestCaseTrait;

    private $pdo = null;
    private $conn = null;
    private $entityManager = null;
    private static $configFile = null;
    private static $inMemory = null;

    /**
     * create silex app
     */
    public function createApplication()
    {
        $app = AppBundle::init($this->getEntityManager(), self::$configFile);
        $app['debug'] = true;

        unset($app['exception_handler']);
        return $app;
    }

    /**
     * Init database connection
     */
    final private function getConnection()
    {
        if (self::$configFile === null) {
            self::$configFile = new ConfigFile();
            self::$configFile->load();
            self::$inMemory = self::$configFile->get("testDbHost") == ":memory:";
        }

        $this->entityManager = AppBundle::createEntityManager(self::$configFile, true);
        $this->pdo = $this->entityManager->getConnection()->getWrappedConnection();

        if (self::$inMemory) {
            $this->conn = $this->createDefaultDBConnection($this->pdo, ':memory:');
        } else {
            $this->conn = $this->createDefaultDBConnection($this->pdo, ':mysql:');
        }

        return $this->conn;
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
        if ($this->entityManager === null) {
            $this->getConnection();
        }

        return $this->entityManager;
    }

    /**
     * Get config file object
     * @return ConfigFile
     */
    final protected function getConfigFile() : ConfigFile
    {
        return self::$configFile;
    }

    /**
     * Migrate database using doctrine
     */
    public function setUp()
    {
        parent::setup();

        // delete possible existing tables if using mysql
        if (!self::$inMemory) {
            $this->dropTables();
        }

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
        $this->pdo->query("set foreign_key_checks=0");

        $tables = $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $sql = "DROP TABLE `$table`";
            $this->pdo->prepare($sql)->execute();
        }

        $this->pdo->query("set foreign_key_checks=1");
    }

    /**
     * Create a test user
     * @param  string $email
     * @param  string $password
     *
     * @return Entity\User   The newly created user
     */
    protected function createTestUser($email="test@example.com", $password="abc123") : Entity\User
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

        return $user;
    }

    /**
     * Login the given user
     * @param  string $email
     * @param  string $password
     * @return string Jwt login token
     */
    protected function loginTestUser($email="test@example.com", $password="abc123")
    {
        // login user
        $client = $this->createClient();
        $crawler = $client->request(
             'POST',
             '/v1/auth/login',
             array(
               "email" => $email,
               "password" => $password)
          );

        $answer = $client->getResponse()->getContent();
        $answer = json_decode($answer, true);

        return $answer['token'];
    }

    /**
     * Create a test Job Category
     * @param  string      $name
     * @return Entity\JobCategory  The created category object
     */
    protected function createTestJobCategory($name="Test Category") : Entity\JobCategory
    {
        // create test user if none exists
        $email = "jobCategoryTestUser@example.com";
        $user = $this->getEntityManager()->find(Entity\User::class, 1);
        if (!$user) {
            $user = $this->createTestUser($email);
        }

        $jobCategory = new Entity\JobCategory();
        $jobCategory->setName($name)
                    ->setUser($user);

        $this->getEntityManager()->persist($jobCategory);
        $this->getEntityManager()->flush();

        return $jobCategory;
    }
}
