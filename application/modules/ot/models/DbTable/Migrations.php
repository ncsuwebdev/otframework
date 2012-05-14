<?php
class Ot_Model_DbTable_Migrations extends Zend_Db_Table
{
    protected $_primary = 'migrationId';
    
    protected $_name = 'tbl_ot_migrations';
    
    protected $_tablePrefix = '';
    
    public function __construct($tablePrefix)
    {
        $this->_name = $tablePrefix . $this->_name;
        $this->_tablePrefix = $tablePrefix;
        
        parent::__construct();
    }
    
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

    public function removeMigration($migrationId) {
        return $this->delete($this->getAdapter()->quoteInto('migrationId = ?', $migrationId));
    }
    
    public function createTable()
    {
        $this->getAdapter()->query("CREATE TABLE IF NOT EXISTS `" . $this->_name . "` (`migrationId` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, UNIQUE (`migrationId`)) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;");
    }
    
    public function dropAllTables()
    {
        $tableList = $this->_getTables();
        
        foreach ($tableList as &$table) {
            $table = '`' . $table . '`';
        }
        
        $tableString = implode(', ', $tableList);
        $this->getAdapter()->query('DROP TABLE ' . $tableString);
    }
    
    protected function _getTables()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        
        $tables = $db->listTables();
        $tableList = array();
        
        foreach ($tables as $t) {
            if (preg_match('/^' . $this->_tablePrefix . '/i', $t)) {
                $tableList[$t] = $t;
            }
        }
        
        return $tableList;
    }
}