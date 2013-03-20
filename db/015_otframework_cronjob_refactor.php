<?php

/**
 * Updates the site to use a variable registry as opposed to xml files
 */

class Db_015_otframework_cronjob_refactor extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query = "ALTER TABLE  `" . $this->tablePrefix . "tbl_ot_cron_status` CHANGE  `name`  `jobKey` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  ''";
        
        $dba->query($query);              
        
    }
    
    public function down($dba) {    
    }    
}