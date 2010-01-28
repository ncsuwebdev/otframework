<?php
/**
 * This file houses the MpmInitController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmInitController initializes the system so that migrations can start happening.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmInitController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MPM_PATH
	 * @uses MPM_METHOD_PDO
	 * @uses MPM_METHOD_MYSQLI
	 * @uses MpmDbHelper::checkForDbTable()
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MpmDbHelper::getMethod()
	 * @uses MpmInitController::displayHelp()
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::writeHeader()
	 * @uses MpmCommandLineWriter::writeFooter()
	 * @uses MpmBuildController::build()
	 * 
	 * @return void
	 */
	public function doAction()
	{
		try {
			if (false === MpmDbHelper::checkForDbTable()) {
			    
				echo "Migrations table not found.\n";
				echo "Creating migrations table... ";
				
				$sql1 = "CREATE TABLE IF NOT EXISTS `" . $db_config->migrationTable . "` ( `id` INT(11) NOT NULL AUTO_INCREMENT, `timestamp` DATETIME NOT NULL, `active` TINYINT(1) NOT NULL DEFAULT 0, `is_current` TINYINT(1) NOT NULL DEFAULT 0, PRIMARY KEY ( `id` ) ) ENGINE=InnoDB";
				$sql2 = "CREATE UNIQUE INDEX `TIMESTAMP_INDEX` ON `" . $db_config->migrationTable . "` ( `timestamp` )";
				
				$pdo = MpmDbHelper::getDbObj();
				$pdo->beginTransaction();
				try
				{
					$pdo->exec($sql1);
					$pdo->exec($sql2);
				}
				catch (Exception $e)
				{
					$pdo->rollback();
					echo "Failure!\n\n" . 'Unable to create required ' . $db_config->migrationTable . ' table:' . $e->getMessage();
					echo "\n\n";
					exit;
				}
				$pdo->commit();

				echo "Done creating migrations table!\n\n";
			} else {
				echo "Migration table found!\n\n";
			}
			
		} catch (Exception $e) {
			echo "Failure!\n\nUnable to complete initialization of migration table: " . $e->getMessage() . "\n\n";
			exit;
		}
	}
}