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
     * Messages array that will be returned to the CLI driver
     */
    protected $_messages = array();
    
    /**
     * Constructor, which initializes the DB connection, available migrations, and applied migrations
     * 
     * @param array $dbConfig
     */
    public function __construct(array $dbConfig, $pathToMigrations)
    {
        $this->_db = Zend_Db::factory($dbConfig['adapter'], array(
            'host'     => $dbConfig['host'],
            'username' => $dbConfig['username'],
            'password' => $dbConfig['password'],
            'dbname'   =>  $dbConfig['dbname']
        ));
        
        Zend_Db_Table::setDefaultAdapter($this->_db);
        
        $this->_migrationsPath = $pathToMigrations;
        
        $this->_availableMigrations = $this->_getAvailableMigrations();
        
        $migrations = new Ot_Migrations();
        $this->_messages[] = "Creating migration table if it doesn't exist.";
        $migrations->createTable(); // create the migrations table if it's needed
        
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
     * Migrates the database from its existing migration version to the $targetMigration
     * 
     * @param $targetMigration
     */
    public function up($targetMigration)
    {
        $returnMessages = array(); 
        
        $migrations = new Ot_Migrations();      
        
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
        $migrations = new Ot_Migrations();
        
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