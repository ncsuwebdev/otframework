<?php

/**
 * Updates the site to use a variable registry as opposed to xml files
 */

class Db_011_otframework_account_attributes extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query ='CREATE TABLE IF NOT EXISTS `' . $this->tablePrefix . 'tbl_ot_account_attribute` (
            `varName` varchar(200) NOT NULL,
            `accountId` int(10) unsigned NOT NULL,
            `value` text NOT NULL,
            PRIMARY KEY (`varName`,`accountId`)
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
        
        $dba->query($query);
    }
    
    public function down($dba) {    
    }    
}