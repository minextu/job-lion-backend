<?php namespace JobLion\JobLion\Database\Migration;

class addASimpleTable extends AbstractMigration
{
    public function upgrade()
    {
        $sql = 'CREATE TABLE `simpleTable` ( `id` INT(255) NULL )';
        return $this->db->getPdo()->prepare($sql)->execute();
    }

    public function downgrade()
    {
        $sql = 'DROP TABLE `simpleTable`';
        return $this->db->getPdo()->prepare($sql)->execute();
    }
}
