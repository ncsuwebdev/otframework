<?php

/**
 * Updates the site to use a variable registry as opposed to xml files
 */

class Db_016_otframework_cleanup extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query = "DROP TABLE IF EXISTS `" . $this->tablePrefix . "tbl_api_log`";
        
        $dba->query($query);   
        
        $query = "DROP TABLE IF EXISTS `" . $this->tablePrefix . "tbl_ot_bug`";
        
        $dba->query($query); 
        
        $query = "DROP TABLE IF EXISTS `" . $this->tablePrefix . "tbl_ot_bug_text`";
        
        $dba->query($query);  
        
        $query = "DROP TABLE IF EXISTS `" . $this->tablePrefix . "tbl_ot_oauth_client_token`";
        
        $dba->query($query);           
        
    }
    
    public function down($dba) {    
    }    
}