<?php
/**
 * This file houses the MpmDbHelper class.
 *
 * @package mysql_php_migrations
 * @subpackage Helpers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmDbHelper class is used to fetch database objects (PDO or Mysqli right now) and perform basic database actions.
 *
 * @package mysql_php_migrations
 * @subpackage Helpers
 */
class MpmDbHelper
{

    /**
     * Returns the correct database object based on the database configuration file.
     *
     * @throws Exception if database configuration file is missing or method is incorrectly defined
     *
     * @uses MpmDbHelper::getPdoObj()
     * @uses MpmDbHelper::getMysqliObj()
     * @uses MpmDbHelper::getMethod()
     * @uses MPM_METHOD_PDO
     * @uses MPM_METHOD_MYSQLI
     *
     * @return object
     */
    static public function getDbObj()
    {
        $pdo_settings = array
        (
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        );
        $db_config = $GLOBALS['db_config'];
        return new PDO("mysql:host={$db_config->host};port={$db_config->port};dbname={$db_config->name}", $db_config->user, $db_config->pass, $pdo_settings);
    }
   
    /**
     * Performs a query; $sql should be a SELECT query that returns exactly 1 row of data; returns an object that contains the row
     *
     * @uses MpmDbHelper::getDbObj()
     * @uses MpmDbHelper::getMethod()
     * @uses MPM_METHOD_PDO
     * @uses MPM_METHOD_MYSQLI
     *
     * @param string $sql a SELECT query that returns exactly 1 row of data
     * @param object $db  a PDO or ExceptionalMysqli object that can be used to run the query
     *
     * @return obj
     */
    static public function doSingleRowSelect($sql, &$db = null)
    {
        try
        {
            if ($db == null)
            {
                $db = MpmDbHelper::getDbObj();
            }

            $stmt = $db->query($sql);
            $obj = $stmt->fetch(PDO::FETCH_OBJ);
            return $obj;
            
        }
        catch (Exception $e)
        {
            echo "\n\nError: ", $e->getMessage(), "\n\n";
            exit;
        }
    }
    
    /**
     * Performs a SELECT query
     *
     * @uses MpmDbHelper::getDbObj()
     * @uses MpmDbHelper::getMethod()
     * @uses MPM_METHOD_PDO
     * @uses MPM_METHOD_MYSQLI
     *
     * @param string $sql a SELECT query
     *
     * @return array
     */
    static public function doMultiRowSelect($sql)
    {
        try
        {
            $db = MpmDbHelper::getDbObj();
            $results = array();

            $stmt = $db->query($sql);
            while ($obj = $stmt->fetch(PDO::FETCH_OBJ)) {
                $results[] = $obj;
            }
            
            return $results;
        }
        catch (Exception $e)
        {
            echo "\n\nError: ", $e->getMessage(), "\n\n";
            exit;
        }
    }
    
    /**
     * Checks to make sure everything is in place to be able to use the migrations tool.
     *
     * @uses MpmDbHelper::getMethod()
     * @uses MpmDbHelper::getPdoObj()
     * @uses MpmDbHelper::getMysqliObj()
     * @uses MpmDbHelper::getMethod()
     * @uses MpmDbHelper::checkForDbTable()
     * @uses MpmCommandLineWriter::getInstance()
     * @uses MpmCommandLineWriter::addText()
     * @uses MpmCommandLineWriter::write()
     * @uses MPM_METHOD_PDO
     * @uses MPM_METHOD_MYSQLI
     *
     * @return void     
     */
    static public function test()
    {
        $problems = array();
        if (!file_exists(MPM_PATH . '/config/db_config.php')) {
            $problems[] = 'You have not yet run the init command.  You must run this command before you can use any other commands.';
        } else {
            if (!class_exists('PDO')) {
                $problems[] = 'It does not appear that the PDO extension is installed.';
            }
            
            $drivers = PDO::getAvailableDrivers();
            
            if (!in_array('mysql', $drivers)) {
                $problems[] = 'It appears that the mysql driver for PDO is not installed.';
            }
            
            if (count($problems) == 0) {
                try
                {
                    $pdo = MpmDbHelper::getDbObj();
                }
                catch (Exception $e)
                {
                    $problems[] = 'Unable to connect to the database: ' . $e->getMessage();
                }
            }
            
            if (!MpmDbHelper::checkForDbTable()) {
                $problems[] = 'Migrations table not found in your database.  Re-run the init command.';
            }
            
            if (count($problems) > 0) {
                $obj = MpmCommandLineWriter::getInstance();
                $obj->addText("It appears there are some problems:");
                $obj->addText("\n");
                foreach ($problems as $problem)
                {
                    $obj->addText($problem, 4);
                    $obj->addText("\n");
                }
                $obj->write();
                exit;
            }
        }
    }

	/**
	 * Checks whether or not the mpm_migrations database table exists.
	 *
     * @uses MpmDbHelper::getDbObj()
     * @uses MpmDbHelper::getMethod()
     * @uses MPM_METHOD_PDO
     * @uses MPM_METHOD_MYSQLI
     * 
	 * @return bool
	 */
	static public function checkForDbTable()
	{
	    $tables = MpmDbHelper::getTables();
	    $db_config = $GLOBALS['db_config'];
		if (count($tables) == 0 || !in_array($db_config->prefix . 'mpm_migrations', $tables))
	    {
	        return false;
	    }
	    return true;
	}
	
	/**
	 * Returns an array of all the tables in the database.
	 *
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MpmDbHelper::getMethod()
	 *
	 * @return array
	 */
	static public function getTables(&$dbObj = null)
	{
	    if ($dbObj == null)
	    {
	        $dbObj = MpmDbHelper::getDbObj();
	    }
   		$sql = "SHOW TABLES";
    	$tables = array();
        try {
    		foreach ($dbObj->query($sql) as $row)
    		{
    			$tables[] = $row[0];
    		}
        } catch (Exception $e) {}
        
		return $tables;
	}
}