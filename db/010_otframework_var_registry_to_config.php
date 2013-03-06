<?php

/**
 * Updates the site to use a variable registry as opposed to xml files
 */

class Db_010_otframework_var_registry_to_config extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query ='RENAME TABLE  `' . $this->tablePrefix . 'tbl_ot_var` TO  `' . $this->tablePrefix . 'tbl_ot_config` ;';
        $dba->query($query);
    }
    
    public function down($dba) {    
    }
    
}