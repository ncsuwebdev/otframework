<?php

/**
 * Updates the site to use a variable registry as opposed to xml files
 */

class Db_012_otframework_trigger_refactor extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query = "RENAME TABLE  `" . $this->tablePrefix . "tbl_ot_trigger_helper_email` TO  `" . $this->tablePrefix . "tbl_ot_trigger_actiontype_email` ;";
        
        $dba->query($query);
        
        $query = "RENAME TABLE  `" . $this->tablePrefix . "tbl_ot_trigger_helper_emailqueue` TO  `" . $this->tablePrefix . "tbl_ot_trigger_actiontype_emailqueue` ;";
        
        $dba->query($query);
        
        $query = "ALTER TABLE  `" . $this->tablePrefix . "tbl_ot_trigger_action` CHANGE  `triggerId`  `eventKey` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  '', CHANGE  `helper`  `actionKey` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT ''";
        
        $dba->query($query);
        
    }
    
    public function down($dba) {    
    }    
}