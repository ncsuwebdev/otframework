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
    }
    
    /**
     * Migrates the database from its existing migration version to the $targetMigration
     * 
     * @param $targetMigration
     */
    public function up($targetMigration)
    {
        
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
}