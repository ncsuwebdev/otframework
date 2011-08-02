<?php

/**
 * Changes ACL from accepting only one role per user to allowing multiple roles
 */

class Db_005_otframework_acl_allow_multiple_roles extends Ot_Migrate_Migration_Abstract
{
    public function up($dba)
    {
        $query ='
        CREATE TABLE `' . $this->tablePrefix . 'tbl_ot_account_roles` (
            `accountId` INT NOT NULL ,
            `roleId` INT NOT NULL,
            PRIMARY KEY(`accountId` , `roleId`)
        ) ENGINE = InnoDB;
        
        INSERT INTO ' . $this->tablePrefix . 'tbl_ot_account_roles (accountId, roleId) (SELECT accountId, role FROM ' . $this->tablePrefix . 'tbl_ot_account);
        
        ALTER TABLE ' . $this->tablePrefix . 'tbl_ot_account DROP `role`;
        
        ';
        $dba->query($query);
    }
    
    public function down($dba) {    
    }
    
}