<?php namespace JobLion\JobLion\Database\Migration;

use JobLion\JobLion\Database\DatabaseInterface;

/**
 * An instance should be able to migrate the Database
 */
abstract class AbstractMigration
{
    /**
     * Database to be migrated
     *
     * @var DatabaseInterface
     */
    protected $db;

    /**
     * Sets the DB
     *
     * @param DatabaseInterface $db Database to be migrated
     */
    final public function setDb(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Upgrade the Database using $this->db
     *
     * @return bool True on success, False otherwise
     */
    abstract public function upgrade();

    /**
     * Dowgrade the Database using $this->db
     *
     * @return bool True on success, False otherwise
     */
    abstract public function downgrade();
}
