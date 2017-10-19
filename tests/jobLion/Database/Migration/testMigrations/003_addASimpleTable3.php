<?php namespace JobLion\JobLion\Database\Migration;

class addASimpleTable3 extends AbstractMigration
{
    public function upgrade()
    {
        $sql = 'CREATE TABLE `simpleTable3` ( `id` INT(255) NULL )';
        return $this->db->getPdo()->prepare($sql)->execute();
    }

    public function downgrade()
    {
        $sql = 'DROP TABLE `simpleTable3`';
        return $this->db->getPdo()->prepare($sql)->execute();
    }
}
