<?php namespace JobLion\Database\Backend\Migration;

class addASimpleTable2 extends AbstractMigration
{
    public function upgrade()
    {
        $sql = 'CREATE TABLE `simpleTable2` ( `id` INT(255) NULL )';
        return $this->db->getPdo()->prepare($sql)->execute();
    }

    public function downgrade()
    {
        $sql = 'DROP TABLE `simpleTable2`';
        return $this->db->getPdo()->prepare($sql)->execute();
    }
}
