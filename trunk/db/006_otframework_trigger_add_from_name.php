<?php

/**
 * Changes ACL from accepting only one role per user to allowing multiple roles
 */

class Db_006_otframework_trigger_add_from_name extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query ='
            ALTER TABLE `' . $this->tablePrefix . 'tbl_ot_trigger_helper_email` ADD `fromName` VARCHAR( 255 ) NOT NULL AFTER `from`;
            
            ALTER TABLE `' . $this->tablePrefix . 'tbl_ot_trigger_helper_emailqueue` ADD `fromName` VARCHAR( 255 ) NOT NULL AFTER `from` ;
        ';
        $dba->query($query);
    }
    
    public function down($dba) {    
    }
    
}