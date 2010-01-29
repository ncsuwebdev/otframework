<?php
class Db_001_otframework_inital_setup extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $dba->query('CREATE TABLE `' . $this->tablePrefix . 'tbl_ot_test_001` (`testId` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, UNIQUE (`testId`)) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');
    }
    
    public function down($dba)
    {
        $dba->query('DROP TABLE `otframework`.`ot_tbl_ot_test_001`');
    }
}