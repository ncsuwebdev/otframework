<?php
class Ot_Migrations extends Ot_Db_Table
{
    protected $_primary = 'migrationId';
    
    protected $_name = 'tbl_ot_migrations';
    
    public function getHighestAppliedMigration()
    {
        return $this->fetchRow(null, 'migrationId DESC', 1)->migrationId;
    }
    
    public function getAppliedMigrations()
    {
        return $this->fetchAll(null, 'migrationId ASC')->toArray();
    }
    
    public function addMigration($migrationId) {
        return $this->insert(array('migrationId' => $migrationId));
    }
}