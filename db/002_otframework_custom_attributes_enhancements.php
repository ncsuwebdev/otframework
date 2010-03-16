<?php

/**
 * Corresponds to version 2.4.0 of the OT Framework
 *
 * Modifies the tbl_ot_custom_attribute and tbl_ot_custom_attribute_value tables
 * to support multiselect and multicheckbox types 
 *
 */
class Db_002_otframework_custom_attributes_enhancements extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        // add multicheckbox and multiselect options to custom attribute types
        $query = "ALTER TABLE `" . $this->tablePrefix . "tbl_ot_custom_attribute` CHANGE `type` `type` ENUM( 'text', 'textarea', 'radio', 'checkbox', 'select', 'ranking', 'multicheckbox', 'multiselect' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'text'";
        $dba->query($query);
        
        // allow the custom attribute value to be null
        $query = "ALTER TABLE `" . $this->tablePrefix . "tbl_ot_custom_attribute_value` CHANGE `value` `value` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL";
        $dba->query($query);
    }
    
    public function down($dba)
    {
        // remove the multiselect and multicheck box options
        $query = "ALTER TABLE `" . $this->tablePrefix . "tbl_ot_custom_attribute` CHANGE `type` `type` ENUM( 'text', 'textarea', 'radio', 'checkbox', 'select', 'ranking' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'text'";
        $dba->query($query);
        
        // remove the ability for the custom attribute value to be null
        $query = "ALTER TABLE `" . $this->tablePrefix . "tbl_ot_custom_attribute_value` CHANGE `value` `value` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ";
        $dba->query($query);
    }
}