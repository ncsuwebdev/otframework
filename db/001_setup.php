<?php
class Db_001_setup implements Ot_Migrate_Migration_Interface
{
    public function up($dba)
    {
        $dba->query('CREATE TABLE `otframework`.`ot_tbl_ot_test` (`testId` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, UNIQUE (`testId`)) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;');
    }
    
    public function down($dba)
    {
        
    }
}