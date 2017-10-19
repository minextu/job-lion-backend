<?php namespace JobLion\JobLion\Database\Migration;

class addUsersTable extends AbstractMigration
{
    public function upgrade()
    {
        $sql = '
        CREATE TABLE `users`
        (
            `id` INT(255) UNSIGNED NULL AUTO_INCREMENT ,
            `email` VARCHAR(100) NOT NULL ,
            `first_name` VARCHAR(30) NOT NULL ,
            `last_name` VARCHAR(30) NOT NULL ,
            `hash` VARCHAR(100) NULL DEFAULT NULL ,
            `create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
            PRIMARY KEY (`id`), UNIQUE (`email`)
        )';

        return $this->db->getPdo()->prepare($sql)->execute();
    }

    public function downgrade()
    {
        $sql = '
        DROP TABLE `users`
        ';

        return $this->db->getPdo()->prepare($sql)->execute();
    }
}
