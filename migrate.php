#!/usr/local/zend/bin/php
<?php
// Path to the application config folder where application.ini and config.xml live
$configFilePath = dirname(__FILE__) . '/application/configs';

// Path to where the database migration files live
$pathToMigrateFiles = dirname(__FILE__) . '/db';



// we want to see any errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

set_include_path(dirname(__FILE__) . '/library' . PATH_SEPARATOR . dirname(__FILE__) . '/application/models' . PATH_SEPARATOR . get_include_path());

require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

$arguments = Ot_Migrate_Cli::validateArgs();

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