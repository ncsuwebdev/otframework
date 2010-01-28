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
        $migrationIds = $this->fetchAll(null, 'migrationId ASC');
        
        $retVal = array();
        foreach ($migrationIds as $i) {
            $retVal[] = $i->migrationId;
        }
        
        return $retVal;
    }
    
    public function addMigration($migrationId) {
        return $this->insert(array('migrationId' => $migrationId));
    }
}