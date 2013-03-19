<?php

/**
 * Updates the site to use a variable registry as opposed to xml files
 */

class Db_013_otframework_customfield_refactor extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query = "ALTER TABLE  `" . $this->tablePrefix . "tbl_ot_custom_attribute` CHANGE  `objectId`  `hostKey` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  '';";
        
        $dba->query($query);
        
        $query = "ALTER TABLE  `" . $this->tablePrefix . "tbl_ot_custom_attribute` DROP  `direction` ;";
        
        $dba->query($query);        
        
        $query = "ALTER TABLE  `" . $this->tablePrefix . "tbl_ot_custom_attribute` ADD  `description` VARCHAR( 255 ) NOT NULL AFTER  `label`";
        
        $dba->query($query);        
        
        $query = "ALTER TABLE  `" . $this->tablePrefix . "tbl_ot_custom_attribute` CHANGE  `type`  `fieldTypeKey` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'text';";
        
        $dba->query($query);       
        
        $query = "ALTER TABLE  `" . $this->tablePrefix . "tbl_ot_custom_attribute_value` CHANGE  `objectId`  `hostKey` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  ''";
        
        $dba->query($query);       
        
        $query = "ALTER TABLE  `" . $this->tablePrefix . "tbl_ot_custom_attribute_value` CHANGE  `parentId`  `hostParentId` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  ''";
        
        $dba->query($query);               
    }
    
    public function down($dba) {    
    }    
}