<?php

/**
 * Updates the site to use a variable registry as opposed to xml files
 */

class Db_009_otframework_remove_theme_module extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query ='Delete from ' . $this->tablePrefix . 'tbl_ot_nav where module="ot" and controller="theme"';
        $dba->query($query);
    }
    
    public function down($dba) {    
    }
    
}