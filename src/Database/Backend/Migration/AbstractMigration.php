<?php namespace JobLion\Database\Backend\Migration;

use JobLion\Database\Backend\BackendInterface;

/**
 * An instance should be able to migrate the Database
 */
abstract class AbstractMigration
{
    /**
     * Database backend to be migrated
     *
     * @var BackendInterface
     */
    protected $db;

    /**
     * Sets the DB
     *
     * @param BackendInterface $db Database Backend to be migrated
     */
    final public function setDb(BackendInterface $db)
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
