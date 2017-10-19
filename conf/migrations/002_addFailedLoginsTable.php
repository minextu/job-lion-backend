<?php namespace JobLion\JobLion\Database\Migration;

class addFailedLoginsTable extends AbstractMigration
{
    public function upgrade()
    {
        $sql = '
        CREATE TABLE `failedLogins`
        (
            `id` INT(255) UNSIGNED NULL AUTO_INCREMENT ,
            `email` VARCHAR(100) NOT NULL ,
            `ip` VARCHAR(100) NOT NULL ,
            `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
            PRIMARY KEY (`id`)
        )';

        return $this->db->getPdo()->prepare($sql)->execute();
    }

    public function downgrade()
    {
        $sql = '
        DROP TABLE `failedLogins`
        ';

        return $this->db->getPdo()->prepare($sql)->execute();
    }
}
