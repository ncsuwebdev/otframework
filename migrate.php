#!/usr/local/zend/bin/php
<?php
/**
 * This file is the main script which should be run on the command line in order to perform database migrations.
 * If you want to use this script like so:  ./migrate.php -- you will need to give it executable permissions (chmod +x migrate.php) and ensure the top line of this script points to the actual location of your PHP binary.
 *
 * @package    mysql_php_migrations
 * @subpackage Globals
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**********************************
 * USER SETABLE VARIABLES
 **********************************/

// Path to the application config folder where application.ini and config.xml live
$configFilePath = dirname(__FILE__) . '/application/configs';

// Path to where the database migration files live
$pathToMigrateFiles = dirname(__FILE__) . '/db';

// Possible environments that you can choose from
$possibleEnvironments = array(
    'production',
    'staging',
    'development',
    'nonproduction',
    'testing',
);

// Name of the table where the migration versions are stored (minus the table prefix)
$migrationTableName = 'tbl_mpm_migration';

/**************** DO NOT EDIT BELOW THIS LINE *******************/

// we want to see any errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

set_include_path(dirname(__FILE__) . '/library' . PATH_SEPARATOR . get_include_path());

if (!isset($argv[1]) || !in_array($argv[1], $possibleEnvironments)) {
    die('Second argument must be one of the following values: (' . implode($possibleEnvironments, ', ') . ')');
}

$environment = $argv[1];
unset($argv[1]);
$argv = array_merge($argv);

$db_config = (object) array();
$db_config->db_path = $pathToMigrateFiles;

require_once 'Zend/Config/Ini.php';
$applicationIni = new Zend_Config_Ini($configFilePath . '/application.ini', $environment);

if (isset($applicationIni->resources->keymanagerdb->key) && $applicationIni->resources->keymanagerdb->key) {
    if (!isset($_SERVER['KEY_MANAGER2_PATH'])) {
        die('KEY_MANAGER2_PATH is not set as a server variable.  Cannot load config information.');
    }
    
    require_once $_SERVER['KEY_MANAGER2_PATH'];
            
    $km = new KeyManager();
        
    if ($km->isKey($applicationIni->resources->keymanagerdb->key)) {
        
        $key = $km->getKey($applicationIni->resources->keymanagerdb->key);
        
        $db_config->host = $key->host;
        $db_config->port = $key->port;
        $db_config->user = $key->username;
        $db_config->pass = $key->password;
        $db_config->name = $key->dbname; 

    } else {
        die('KeyManager could not find the key: ' . $applicationIni->resources->keymanagerdb->key);
    }
} else {
    if (!isset($applicationIni->resources->db)) {
        die('DB resources not found in application.ini');
    }
    
    $db_config->host = $applicationIni->resources->db->params->host;
    $db_config->port = $applicationIni->resources->db->params->port;
    $db_config->user = $applicationIni->resources->db->params->username;
    $db_config->pass = $applicationIni->resources->db->params->password;
    $db_config->name = $applicationIni->resources->db->params->dbname; 
}

require_once 'Zend/Config/Xml.php';
$configXml = new Zend_Config_Xml($configFilePath . '/config.xml', 'production');

$db_config->migrationTable = $configXml->app->tablePrefix . $migrationTableName;

/** 
 * Define the full path to this file.
 */
define('MPM_PATH', dirname(__FILE__) . '/migrate');

/**
 * Version Number - for reference
 */
define('MPM_VERSION', '2.0.1');

/**
 * Include the init script.
 */
require_once(MPM_PATH . '/lib/init.php');

// get the proper controller, do the action, and exit the script
$obj = MpmControllerFactory::getInstance($argv);
$obj->doAction();
exit;