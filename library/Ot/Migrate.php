<?php
class Ot_Migrate
{
    /**
     * Zend_Db Adapter Object
     * 
     * @var Zend_Db adapter
     */
    protected $_db;
    
    /**
     * Array of available migration files
     * 
     * @var array
     */
    protected $_availableMigrations = array();
    
    /**
     * Array of applied migrations
     * 
     * @var array
     */
    protected $_appliedMigrations = array();
    
    /**
     * File path to the folder where the migrations reside
     * 
     * @var string
     */
    protected $_migrationsPath = '';
    
    /**
     * Constructor, which initializes the DB connection, available migrations, and applied migrations
     * 
     * @param array $dbConfig
     */
    public function __construct(array $dbConfig, $pathToMigrations)
    {
        $this->_db = Zend_Db::factory($dbConfig['adapter'], array(
            'host'     => $dbConfig['hostname'],
            'username' => $dbConfig['username'],
            'password' => $dbConfig['password'],
            'dbname'   =>  $dbConfig['dbName']
        ));
        
        $this->_migrationsPath = $pathToMigrations;
        
        $this->_availableMigrations = $this->_getAvailableMigrations();
        
        $migrations = new Ot_Migrations();
        
        $this->_appliedMigrations = $migrations->getAppliedMigrations();
        
        /**
         * To create table
         * 
         * CREATE TABLE `otframework`.`ot_tbl_ot_migrations` (`migrationId` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, UNIQUE (`migrationId`)) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
         */
        
    }
    
    /**
     * Migrates the database from its existing migration version to the $targetMigration
     * 
     * @param $targetMigration
     */
    public function up($targetMigration)
    {
        $migrations = new Ot_Migrations();
        
        $migrationsIdsNotApplied = array_diff(array_keys($this->_availableMigrations), $this->_appliedMigrations);
        
        $migrationsToApply = array();
        foreach ($migrationsIdsNotApplied as $migrationId) {
            if ($migrationId <= $targetMigration) {
                $migrationsToApply[] = $this->_availableMigrations[$migrationId];   
            }
        }
        
        $this->_db->beginTransaction();
        
        foreach ($migrationsToApply as $m) {
            require_once($this->_migrationsPath . '/' . $m);
            $classname = substr($m, 0, -4); //strip out the .php extension
            $migrationClass = new $classname;

            try {
                $migrationClass->up($this->_db);
            } catch (Exception $e) {
                $this->_db->rollback();
                throw new Exception('Error applying migration ' . $m . '. ' . $e->getMessage());
            }
            
            try {
                $migrations->addMigration($this->_getMigrationIdFromFilename($m));
            } catch (Exception $e) {
                $this->_db->rollback();
                throw new Exception('Migration ' . $m . ' was successful, but adding record to migrations table failed. ' . $e->getMessage());
            }
        }
        
        $this->_db->commit();
    }
    
    /**
     * Migrates the database from its existing migration down to the $targetMigration
     * 
     * @param $targetMigration
     */
    public function down($targetMigration)
    {
        
    }
    
    /**
     * Migrates the database from its existing migration to the latest available migration
     * 
     */
    public function latest()
    {
        $highestAvailableMigration = $this->_availableMigrations[count($this->_availableMigrations) - 1];
        $this->up($highestAvailableMigration);
    }
    
    /**
     * Executes the down() method to the earliest possible migration, then rebuilds the
     * database to the latest version unless another $targetMigration is specified
     * 
     * @param mixed $targetMigration
     */
    public function rebuild($targetMigration = null)
    {
        
    }
        
    /**
     * Gets the array of all the available migration scripts
     */
    protected function _getAvailableMigrations()
    {
        $availableMigrations = array();
        
        $files = scandir($this->_migrationsPath);
        
        foreach ($files as $filename) {
            
            if (substr($filename, 0, -4) == '.php' && $filename != '.' && $filename != '..' && !is_dir($filename)) {
            
                $id = $this->_getMigrationIdFromFilename($filename);
                $availableMigrations[$id] = $filename;
            }
        }
        
        return $availableMigrations;
    }
    
    /**
     * Gets the id of a migration from its filename
     * 
     * @param string The filename to extract the id from
     * @return string The id of the migration
     */
    protected function _getMigrationIdFromFilename($filename) {
        $parts = explode('_', $filename);
        return $parts[0];
    }
}