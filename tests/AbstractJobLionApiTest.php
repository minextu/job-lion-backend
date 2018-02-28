<?php namespace JobLion;

use Silex\WebTestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use PDO;
use Doctrine\ORM\Tools\SchemaTool;

use JobLion\AppBundle\AppBundle;
use JobLion\AppBundle\EntityManager;
use JobLion\AppBundle\ConfigFile;
use JobLion\AuthBundle\Password;
use JobLion\AppBundle\Entity\User;
use JobLion\AppBundle\Entity\JobCategory;
use JobLion\ExperienceReportBundle\Entity\ExperienceReport;
use JobLion\CompanyBundle\Entity\Company;

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
        $app['isTest'] = true;
        $_SERVER['SERVER_NAME'] = "phpunit.text.example.com";

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
     * @return User   The newly created user
     */
    protected function createTestUser($email="test@example.com", $password="abc123", $activated=true) : User
    {
        $user = new User();

        // additional information
        $firstName = "Test";
        $lastName = "Testus";

        // save user
        $user->setEmail($email)
             ->setFirstName($firstName)
             ->setLastName($lastName)
             ->setHash(Password::hash($password))
             ->setActivated($activated);

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
     * @return JobCategory        The created category object
     */
    protected function createTestJobCategory($name="Test Category") : JobCategory
    {
        // create test user if none exists
        $email = "jobCategoryTestUser@example.com";
        $user = $this->getEntityManager()->find(User::class, 1);
        if (!$user) {
            $user = $this->createTestUser($email);
        }

        $jobCategory = new JobCategory();
        $jobCategory->setName($name)
                    ->setUser($user);

        $this->getEntityManager()->persist($jobCategory);
        $this->getEntityManager()->flush();

        return $jobCategory;
    }

    /**
     * Create a test report
     * @return ExperienceReport
     */
    protected function createTestReport($name="Test Report") : ExperienceReport
    {
        // create test category
        $category = $this->createTestJobCategory("Test Category 1");

        // create and login test user
        $user = $this->createTestUser();
        $token = $this->loginTestUser();

        // create test report
        $report = new ExperienceReport();
        $report->setTitle($name)
               ->setText("Test Report Text")
               ->setJobCategories([$category])
               ->setUser($user);

        $this->getEntityManager()->persist($report);
        $this->getEntityManager()->flush();

        return $report;
    }

    /**
     * Create a test company
     * @return Company
     */
    protected function createTestCompany($name="Test Company") : Company
    {
        // create and login test user
        $email = "companyTest@example.com";
        $user = $this->getEntityManager()
                     ->getRepository(User::class)
                     ->findOneBy(array('email' => $email));

        if (!$user) {
            $user = $this->createTestUser($email);
        }
        $token = $this->loginTestUser($email);

        // create test company
        $company = new Company();
        $company->setTitle($name)
                ->setUser($user);

        $this->getEntityManager()->persist($company);
        $this->getEntityManager()->flush();

        return $company;
    }
}
