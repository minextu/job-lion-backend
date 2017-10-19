<?php namespace JobLion\Database\Backend\Migration;

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use JobLion\Database\Backend;

class MigratorTest extends TestCase
{
    use TestCaseTrait;

    // only instantiate pdo once for test clean-up/fixture load
    private static $pdo = null;

    // only instantiate DatabaseConnection once per test
    private $conn = null;

    public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO('sqlite::memory:');
            }

            $this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
        }

        return $this->conn;
    }

    public function getDataSet()
    {
        return new \PHPUnit\DbUnit\DataSet\DefaultDataSet();
    }

    public function getDb()
    {
        return new Backend\Fake($this->getConnection()->getConnection());
    }

    public function testDatabaseCanBeUpgradedUsingAnObject()
    {
        $migrator = new Migrator(0, 0, $this->getDb());

        require_once("testMigrations/001_addASimpleTable.php");
        $migrationObject = new addASimpleTable();

        // upgrade
        $status = $migrator->migrateObject($migrationObject, false);
        $this->assertTrue($status);

        // check if table was created
        $tables = $this->getConnection()->createDataSet();
        $expectedTables = $this->createXMLDataSet(__DIR__.'/testMigrations/001.xml');
        $this->assertDataSetsEqual($expectedTables, $tables);
    }

    public function testDatabaseCanBeDowngradedUsingAnObject()
    {
        $migrator = new Migrator(0, 0, $this->getDb());
        require_once("testMigrations/001_addASimpleTable.php");
        $migrationObject = new addASimpleTable();

        // downgrade
        $status = $migrator->migrateObject($migrationObject, true);
        $this->assertTrue($status);

        // check if table was deleted
        $tables = $this->getConnection()->createDataSet();
        $expectedTables = new \PHPUnit\DbUnit\DataSet\DefaultDataSet();
        $this->assertDataSetsEqual($expectedTables, $tables);
    }

    public function testDatabaseCanBeUpgradedUsingAFolder()
    {
        // simulate an Upgrade from Version 0 to 3
        $currentVersion = 0;
        $targetVersion = 3;

        $migrator = new Migrator($currentVersion, $targetVersion, $this->getDb());

        // start migration, this should upgrade 001, 002 and 003
        $status = $migrator->migrateFolder(dirname(__FILE__)."/testMigrations");
        $this->assertTrue($status);

        // check if tables were created
        $tables = $this->getConnection()->createDataSet();
        $expectedTables = $this->createXMLDataSet(__DIR__.'/testMigrations/003.xml');
        $this->assertDataSetsEqual($expectedTables, $tables);
    }

    public function testDatabaseCanBeDowngradedUsingAFolder()
    {
        // simulate an Downgrade from Version 3 to 1
        $currentVersion = 3;
        $targetVersion = 1;

        $migrator = new Migrator($currentVersion, $targetVersion, $this->getDb());

        // start migration, this should downgrade 003 and 002
        $status = $migrator->migrateFolder(dirname(__FILE__)."/testMigrations");
        $this->assertTrue($status);

        // check if tables were deleted
        $tables = $this->getConnection()->createDataSet();
        $expectedTables = $this->createXMLDataSet(__DIR__.'/testMigrations/001.xml');
        $this->assertDataSetsEqual($expectedTables, $tables);
    }
}
