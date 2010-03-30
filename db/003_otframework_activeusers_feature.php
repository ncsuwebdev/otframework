<?php

/**
 * Corresponds to version 2.4.0 of the OT Framework
 * 
 * Adds the database table to support the active users feature
 *
 */
class Db_003_otframework_activeusers_feature extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query = "CREATE TABLE `" . $this->tablePrefix . "tbl_ot_active_user` (`accountId` INT UNSIGNED NOT NULL , `dt` INT UNSIGNED NOT NULL , PRIMARY KEY ( `accountId` )) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;";
        $dba->query($query);
    }
    
    public function down($dba)
    {       
        $query = "DROP TABLE `" . $this->tablePrefix . "tbl_ot_active_user`";
        $dba->query($query);
    }
}