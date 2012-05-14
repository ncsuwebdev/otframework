<?php

require_once(APPLICATION_PATH . '/modules/ot/models/DbTable/Migrations.php');

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
     * Messages array that will be returned to the CLI driver
     */
    protected $_messages = array();
    
    /**
     * The database table prefix to be used
     */
    protected $_tablePrefix = '';
    
    /**
     * Constructor, which initializes the DB connection, available migrations, and applied migrations
     *
     * @param array $dbConfig
     */
    public function __construct($dbAdapter, $pathToMigrations, $tablePrefix = '')
    {
        $this->_db = Zend_Db::factory('pdo_mysql', $dbAdapter->getConfig());
        
        Zend_Db_Table::setDefaultAdapter($this->_db);
        
        $this->_tablePrefix = $tablePrefix;
        
        $this->_migrationsPath = $pathToMigrations;
        
        $this->_availableMigrations = $this->_getAvailableMigrations();

        $migrations = new Ot_Model_DbTable_Migrations($this->_tablePrefix);
        $this->_messages[] = "Creating migration table if it doesn't exist.";
        $migrations->createTable(); // create the migrations table if it's needed
        $this->_messages[] = "Migration table created successfully if it needed to be.";
        
        $this->_appliedMigrations = $migrations->getAppliedMigrations();
    }
    
    /**
     * Runs the given command (method) passed from the commandline
     *
     * @param string The method to run
     * @param string The version to migrate to, if applicable
     */
    public function migrate($method, $version)
    {
        $this->$method($version);
        return $this->_messages;
    }
    
    /**
     * Just creates the migration table and doesn't run any migration scripts.
     * We really only need to just return true since the constructor will create
     * the table for us if it doesn't exist.
     */
    public function createtable()
    {
        return $this->_messages;
    }
    
    /**
     * This method is used to start from a specific version rather than from 001.
     * So if your database is already at 001 and you want to run the migrations
     * from 002 up to the latest, this will insert the migration ids from 001 up
     * to the version passed in.  You can only use this method if the migrations
     * table is empty.  This doesn't actually apply the migrations.
     *
     * @param $latestVersion The version the migration system should think it's at.
     */
    public function setlatestversion($latestVersion)
    {
        $migrations = new Ot_Model_DbTable_Migrations($this->_tablePrefix);
        
        if (count($this->_appliedMigrations) > 0) {
            $this->_messages[] = 'You can only run this method on an empty migrations table';
            return;
        }
        
        $migrationsIdsNotApplied = array_diff(array_keys($this->_availableMigrations), $this->_appliedMigrations);
        
        $migrationsToApply = array();
        
        foreach ($migrationsIdsNotApplied as $migrationId) {
            if ($migrationId <= $latestVersion) {
                $migrationsToApply[] = $this->_availableMigrations[$migrationId];
            }
        }
        
        if (empty($migrationsToApply)) {
            $this->_messages[] = 'No migrations to apply';
            return;
        }
        
        $this->_db->beginTransaction();
        
        foreach ($migrationsToApply as $m) {
           
            try {
                $migrations->addMigration($this->_getMigrationIdFromFilename($m));
            } catch (Exception $e) {
                $this->_db->rollback();
                throw new Exception('Adding migration record to migration table for version ' . $m . ' failed. ' . $e->getMessage());
            }
            
            $this->_messages[] = 'Added migration record to migration table for version ' . $m;
        }
        
        $this->_db->commit();
    }
    
    /**
     * Migrates the database from its existing migration version to the $targetMigration
     *
     * @param $targetMigration
     */
    public function up($targetMigration)
    {
        $migrations = new Ot_Model_DbTable_Migrations($this->_tablePrefix);
        
        $migrationsIdsNotApplied = array_diff(array_keys($this->_availableMigrations), $this->_appliedMigrations);
        
        $migrationsToApply = array();
        
        foreach ($migrationsIdsNotApplied as $migrationId) {
            if ($migrationId <= $targetMigration) {
                $migrationsToApply[] = $this->_availableMigrations[$migrationId];
            }
        }
        
        if (empty($migrationsToApply)) {
            $this->_messages[] = 'No migrations to apply';
            return;
        }
        
        $this->_db->beginTransaction();
        
        foreach ($migrationsToApply as $m) {
            
            require_once $this->_migrationsPath . '/' . $m;
            $classname = 'Db_' . substr($m, 0, -4); //strip out the .php extension
            $migrationClass = new $classname(array('tablePrefix' => $this->_tablePrefix));

            try {
                $migrationClass->up($this->_db, $this->_tablePrefix);
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
            
            $this->_messages[] = 'Applied ' . $m;
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
        $migrationsModel = new Ot_Model_DbTable_Migrations($this->_tablePrefix);
        
        end($this->_appliedMigrations);
        $highestExecutedMigration = current($this->_appliedMigrations);
        reset($this->_appliedMigrations);

        if (!isset($this->_availableMigrations[$targetMigration])) {
            throw new Exception('The requested migration ' . $targetMigration . ' was not found.');
        }
        
        if ((int)$highestExecutedMigration <= (int)$targetMigration) {
            throw new Exception('The database is only migrated to migration ' . $highestExecutedMigration . ', which is before the requested migration ' . $targetMigration);
        }
        
        $migrationsToApply = array();
        
        foreach ($this->_appliedMigrations as $a) {
            if ((int)$a > (int)$targetMigration && isset($this->_availableMigrations[$a])) {
                $migrationsToApply[] = $this->_availableMigrations[$a];
            }
        }
        
        $migrationsToApply = array_reverse($migrationsToApply);
        
        if (empty($migrationsToApply)) {
            $this->_messages[] = 'No migrations to apply';
        }
        
        $this->_db->beginTransaction();
        
        foreach ($migrationsToApply as $m) {
            require_once $this->_migrationsPath . '/' . $m;
            $classname = 'Db_' . substr($m, 0, -4); //strip out the .php extension
            $migrationClass = new $classname(array('tablePrefix' => $this->_tablePrefix));

            try {
                $migrationClass->down($this->_db, $this->_tablePrefix);
            } catch (Exception $e) {
                $this->_db->rollback();
                throw new Exception('Error applying migration ' . $m . '. ' . $e->getMessage());
            }
            
            try {
                $migrationsModel->removeMigration($this->_getMigrationIdFromFilename($m));
            } catch (Exception $e) {
                $this->_db->rollback();
                throw new Exception('Migration ' . $m . ' was successful, but adding record to migrations table failed. ' . $e->getMessage());
            }
            
            $this->_messages[] = 'Down migration of ' . $m . ' was successful.';
        }
        
        $this->_db->commit();
    }
    
    /**
     * Migrates the database from its existing migration to the latest available migration
     *
     */
    public function latest()
    {
        end($this->_availableMigrations);
        $highestAvailableMigration = current($this->_availableMigrations);
        reset($this->_availableMigrations);
        return $this->up($highestAvailableMigration);
    }
    
    /**
     * Executes the down() method to the earliest possible migration, then rebuilds the
     * database to the latest version unless another $targetMigration is specified
     *
     * @param mixed $targetMigration
     */
    public function rebuild($targetMigration = null)
    {
        $migrations = new Ot_Model_DbTable_Migrations($this->_tablePrefix);
        
        $this->_messages[] = 'Dropping all tables';
        
        try {
            $migrations->dropAllTables();
        } catch (Exception $e) {
            throw new Exception('Dropping all the tables failed. ' . $e->getMessage());
        }
        
        $this->_messages[] = 'All tables dropped.';
        
        $this->_messages[] = "Recreating migration table.";
        $migrations->createTable(); // create the migrations table
        
        // reset the applied migrations array.  it should be empty, but we'll
        // check the db just to make sure
        $this->_appliedMigrations = $migrations->getAppliedMigrations();
              
        if (is_null($targetMigration)) {
            $this->_messages[] = 'Rebuilding database to latest version.';
            return $this->latest();
        } else {
            $this->_messages[] = 'Rebuilding database to version ' . $targetMigration . '.';
            return $this->up($targetMigration);
        }
    }
        
    /**
     * Gets the array of all the available migration scripts
     */
    protected function _getAvailableMigrations()
    {
        $availableMigrations = array();
        
        $files = scandir($this->_migrationsPath);
        
        foreach ($files as $filename) {
            
            if (substr($filename, -4) == '.php' && $filename != '.' && $filename != '..' && !is_dir($filename)) {
            
                $id = $this->_getMigrationIdFromFilename($filename);
                $availableMigrations[$id] = $filename;
            }
        }
        
        if (empty($availableMigrations)) {
            throw new Exception('No available migrations found');
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