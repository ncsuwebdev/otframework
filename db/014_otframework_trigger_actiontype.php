<?php

/**
 * Updates the site to use a variable registry as opposed to xml files
 */

class Db_014_otframework_trigger_actiontype extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query = "UPDATE " . $this->tablePrefix . "tbl_ot_trigger_action SET actionKey =  'Ot_Trigger_ActionType_Email' WHERE actionKey =  'Ot_Trigger_Plugin_Email'";
        
        $dba->query($query);
        
        $query = "UPDATE " . $this->tablePrefix . "tbl_ot_trigger_action SET actionKey =  'Ot_Trigger_ActionType_EmailQueue' WHERE actionKey =  'Ot_Trigger_Plugin_EmailQueue'";
        
        $dba->query($query);        
        
    }
    
    public function down($dba) {    
    }    
}