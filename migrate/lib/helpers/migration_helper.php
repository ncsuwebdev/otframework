<?php
/**
 * This file houses the MpmMigrationHelper class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmMigrationHelper contains a number of static functions which are used during the migration process.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmMigrationHelper
{
    
    /**
     * Sets the current active migration.
     *
     * @uses MpmDbHelper::getDbObj()
     *
     * @param int $id the ID of the migration to set as the current one
     *
     * @return void
     */
    static public function setCurrentMigration($id)
    {
        $db_config = $GLOBALS['db_config'];
        $sql1 = "UPDATE " . $db_config->prefix . "`mpm_migrations` SET `is_current` = '0'";
        $sql2 = "UPDATE " . $db_config->prefix . "`mpm_migrations` SET `is_current` = '1' WHERE `id` = {$id}";
        $obj = MpmDbHelper::getDbObj();
        $obj->beginTransaction();
        try { 
	        $obj->exec($sql1);
	        $obj->exec($sql2);
        } catch (Exception $e) {
	        $obj->rollback();
	        echo "\n\tQuery failed!";
	        echo "\n\t--- " . $e->getMessage();
	        exit;
        }
        $obj->commit();
    }
    
	
	/**
	 * Performs a single migration.
	 *
	 * @uses MpmStringHelper::getFilenameFromTimestamp()
	 * @uses MpmDbHelper::getPdoObj()
	 * @uses MpmDbHelper::getMysqliObj()
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::writeLine()
	 * @uses MPM_DB_PATH
	 *
	 * @param object  $obj        		    a simple object with migration information (from a migration list)
	 * @param int    &$total_migrations_run a running total of migrations run
	 * @param bool    $forced               if true, exceptions will not cause the script to exit
	 *
	 * @return void
	 */
	static public function runMigration(&$obj, $method = 'up', $forced = false)
	{
	    $db_config = $GLOBALS['db_config'];
		$filename = MpmStringHelper::getFilenameFromTimestamp($obj->timestamp);
		$classname = 'Migration_' . str_replace('.php', '', $filename);
		
	    // make sure the file exists; if it doesn't, skip it but display a message
	    if (!file_exists(MPM_DB_PATH . $filename)) {
	        echo "\n\tMigration " . $obj->timestamp . ' (ID '.$obj->id.') skipped - file missing.';
	        return;
	    }
	    
	    // file exists -- run the migration
		echo "\n\tPerforming " . strtoupper($method) . " migration " . $obj->timestamp . ' (ID '.$obj->id.')... ';
		require_once(MPM_DB_PATH . $filename);
		$migration = new $classname();
        if ($migration instanceof MpmMigration) {// need PDO object
            $dbObj = MpmDbHelper::getDbObj();
        }
        
   		$dbObj->beginTransaction();
		if ($method == 'down') {
			$active = 0;
		} else {
			$active = 1;
		} 
		
		try {
			$migration->$method($dbObj);
			$sql = "UPDATE " . $db_config->prefix . "`mpm_migrations` SET `active` = '$active' WHERE `id` = {$obj->id}";
			$dbObj->exec($sql);
		} catch (Exception $e) {
			$dbObj->rollback();
			echo "failed!";
			echo "\n";
		    $clw = MpmCommandLineWriter::getInstance();
    		$clw->writeLine($e->getMessage(), 12);
			if (!$forced) {
        		echo "\n\n";
			    exit;
			} else {
			    return;
		    }
		}
		$dbObj->commit();
		echo "done.";
	}

	/**
	 * Returns the timestamp of the migration currently rolled to.
	 *
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MpmDbHelper::getMethod()
	 * @uses MPM_METHOD_PDO
	 * @uses MPM_METHOD_MYSQLI
	 *
	 * @return string
	 */
	static public function getCurrentMigrationTimestamp()
	{
	    $db_config = $GLOBALS['db_config'];
	    // Resolution to Issue #1 - PDO::rowCount is not reliable
	    $sql1 = "SELECT COUNT(*) as total FROM " . $db_config->prefix . "`mpm_migrations` WHERE `is_current` = 1";
	    $sql2 = "SELECT `timestamp` FROM " . $db_config->prefix . "`mpm_migrations` WHERE `is_current` = 1";
		$dbObj = MpmDbHelper::getDbObj();

		$stmt = $dbObj->query($sql1);
        if ($stmt->fetchColumn() == 0) {
            return false;
        }
        unset($stmt);
        $stmt = $dbObj->query($sql2);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $latest = $row['timestamp'];

		return $latest;
	}
	
	/**
	 * Returns an array of migrations which need to be run (in order).
	 *
	 * @uses MpmMigrationHelper::getTimestampFromId()
	 * @uses MpmDbHelper::getMethod()
	 * @uses MpmDbHelper::getPdoObj()
	 * @uses MpmDbHelper::getMysqliObj()
	 * @uses MPM_METHOD_MYSQLI
	 * @uses MPM_METHOD_PDO
	 *
	 * @param int    $toId      the ID of the migration to stop on
	 * @param string $direction the direction of the migration; should be 'up' or 'down'
	 *
	 * @return array
	 */
	static public function getListOfMigrations($toId, $direction = 'up')
	{
	    $db_config = $GLOBALS['db_config'];
	    $list = array();
	    $timestamp = MpmMigrationHelper::getTimestampFromId($toId);
	    if ($direction == 'up') {
	        $sql = "SELECT `id`, `timestamp` FROM " . $db_config->prefix . "`mpm_migrations` WHERE `active` = 0 AND `timestamp` <= '$timestamp' ORDER BY `timestamp`";
	    } else {
	        $sql = "SELECT `id`, `timestamp` FROM " . $db_config->prefix . "`mpm_migrations` WHERE `active` = 1 AND `timestamp` > '$timestamp' ORDER BY `timestamp` DESC";
	    }
	    
        try {
    		$pdo = MpmDbHelper::getDbObj();
            $stmt = $pdo->query($sql);
            while ($obj = $stmt->fetch(PDO::FETCH_OBJ)) {
                $list[$obj->id] = $obj;
            }
        } catch (Exception $e) {
            echo "\n\nError: " . $e->getMessage() . "\n\n";
            exit;
        }

        return $list;
	}

    /**
     * Returns a timestamp when given a migration ID number.
     *
     * @uses MpmDbHelper::getMethod()
     * @uses MpmDbHelper::getPdoObj()
     * @uses MpmDbHelper::getMysqliObj()
     * @uses MPM_METHOD_MYSQLI
     * @uses MPM_METHOD_PDO
     *
     * @param int $id the ID number of the migration
     *
     * @return string
     */
    static public function getTimestampFromId($id)
    {
        $db_config = $GLOBALS['db_config'];
        
        try {
    	    // Resolution to Issue #1 - PDO::rowCount is not reliable
       	    $pdo = MpmDbHelper::getDbObj();
    	    $sql = "SELECT COUNT(*) FROM " . $db_config->prefix . "`mpm_migrations` WHERE `id` = '$id'";
    	    $stmt = $pdo->query($sql);
    	    if ($stmt->fetchColumn() == 1) {
    	        unset($stmt);
        	    $sql = "SELECT `timestamp` FROM " . $db_config->prefix . "`mpm_migrations` WHERE `id` = '$id'";
        	    $stmt = $pdo->query($sql);
    	        $result = $stmt->fetch(PDO::FETCH_OBJ);
    	        $timestamp = $result->timestamp;
            } else {
                $timestamp = false;
            }
	    } catch (Exception $e) {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
        }
	    return $timestamp;
    }

	/**
	 * Returns the number of the migration currently rolled to.
	 *
	 * @uses MpmDbHelper::getMethod()
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MPM_METHOD_MYSQLI
	 * @uses MPM_METHOD_PDO
	 *
	 * @return string
	 */
	static public function getCurrentMigrationNumber()
	{
	    $db_config = $GLOBALS['db_config'];
	     
	    try {
            $pdo = MpmDbHelper::getDbObj();
            // Resolution to Issue #1 - PDO::rowCount is not reliable
            $sql = "SELECT COUNT(*) FROM " . $db_config->prefix . "`mpm_migrations` WHERE `is_current` = 1";
            $stmt = $pdo->query($sql);
            if ($stmt->fetchColumn() == 0) {
                return false;
            }
            $sql = "SELECT `id` FROM " . $db_config->prefix . "`mpm_migrations` WHERE `is_current` = 1";
            unset($stmt);
            $stmt = $pdo->query($sql);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $latest = $row['id'];
	    } catch (Exception $e) {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
        return $latest;
	}
	
	/**
	 * Returns the total number of migrations.
	 *
	 * @uses MpmDbHelper::getMethod()
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MPM_METHOD_MYSQLI
	 * @uses MPM_METHOD_PDO
	 *
	 * @return int
	 */
	static public function getMigrationCount()
	{
	    $db_config = $GLOBALS['db_config'];
	    
	    try {
	        $pdo = MpmDbHelper::getDbObj();
    	    // Resolution to Issue #1 - PDO::rowCount is not reliable
	        $sql = "SELECT COUNT(id) FROM " . $db_config->prefix . "`mpm_migrations`";
	        $stmt = $pdo->query($sql);
	        $count = $stmt->fetchColumn();
	    } catch (Exception $e) {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
	    return $count;
	}
	
	/**
	 * Returns the ID of the latest migration.
	 *
	 * @uses MpmDbHelper::getMethod()
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MPM_METHOD_MYSQLI
	 * @uses MPM_METHOD_PDO
	 *
	 * @return int
	 */
	static public function getLatestMigration()
	{
	    $db_config = $GLOBALS['db_config'];
        
	    $sql = "SELECT `id` FROM " . $db_config->prefix . "`mpm_migrations` ORDER BY `timestamp` DESC LIMIT 0,1";
	    try {
            $pdo = MpmDbHelper::getDbObj();
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            $to_id = $result->id;
	    } catch (Exception $e) {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
	    return $to_id;
	}
	
	/**
	 * Checks to see if a migration with the given ID actually exists.
	 *
	 * @uses MpmDbHelper::getMethod()
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MPM_METHOD_MYSQLI
	 * @uses MPM_METHOD_PDO
	 *
	 * @param int $id the ID of the migration
	 *
	 * @return int
	 */
	static public function doesMigrationExist($id)
	{
	    $db_config = $GLOBALS['db_config'];
        
	    $sql = "SELECT COUNT(*) as total FROM " . $db_config->prefix . "`mpm_migrations` WHERE `id` = '$id'";
        $return = false;
	    try {
            $pdo = MpmDbHelper::getDbObj();
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            if ($result->total > 0) {
                $return = true;
            }
	    } catch (Exception $e) {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
	    return $return;
	}
	
	/**
	 * Returns a migration object; this object contains all data stored in the DB for the particular migration ID.
	 *
	 * @uses MpmDbHelper::getMethod()
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MPM_METHOD_MYSQLI
	 * @uses MPM_METHOD_PDO
	 *
	 * @param int $id the ID of the migration
	 *
	 * @return object
	 */
	static public function getMigrationObject($id)
	{
	    $db_config = $GLOBALS['db_config'];
		
	    $sql = "SELECT * FROM " . $db_config->prefix . "`mpm_migrations` WHERE `id` = '$id'";
		$obj = null;
	    try
	    {
            $pdo = MpmDbHelper::getDbObj();
            $stmt = $pdo->query($sql);
            $obj = $stmt->fetch(PDO::FETCH_OBJ);
	    } catch (Exception $e) {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
	    return $obj;
	}
}