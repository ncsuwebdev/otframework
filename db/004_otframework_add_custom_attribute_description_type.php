<?php

/**
 * Adds support for the 'description' custom attribute
 *
 */
class Db_004_otframework_add_custom_attribute_description_type extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query = "ALTER TABLE  `" . $this->tablePrefix . "tbl_ot_custom_attribute` CHANGE  `type`  `type` ENUM(  'text',  'textarea',  'radio',  'checkbox',  'select',  'ranking',  'multicheckbox',  'multiselect',  'description' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'text'";
        $dba->query($query);
    }
    
    public function down($dba)
    {       
        $query = "DROP TABLE `" . $this->tablePrefix . "tbl_ot_active_user`";
        $dba->query($query);
    }
}