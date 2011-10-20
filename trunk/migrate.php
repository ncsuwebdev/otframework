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

$possibleEnvironments = array(
    'production',
    'staging',
    'development',
    'nonproduction',
    'testing'
);

$possibleCommands = array(
    'up',
    'down',
    'latest',
    'rebuild',
    'createtable',
    'setlatestversion'
);

$opts = new Zend_Console_Getopt(
    array(
        'cmd|c=s'         => 'Command to execute (' . implode($possibleCommands, ', ') . ')',
        'environment|e=s' => 'Environment to migrate (' . implode($possibleEnvironments, ', ') . ')',
        'version|v=s'     => 'Version to migrate to'
    )
);

try {
    $opts->parse();
} catch (Exception $e) {
    Ot_Migrate_Cli::error($opts->getUsageMessage());
}

if (!isset($opts->environment) || !in_array($opts->environment, $possibleEnvironments)) {
    Ot_Migrate_Cli::error('Environment not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

if (!isset($opts->cmd) || !in_array($opts->cmd, $possibleCommands)) {
    Ot_Migrate_Cli::error('Command not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

$applicationIni = new Zend_Config_Ini($configFilePath . '/application.ini', $opts->environment);

if (!isset($applicationIni->resources->db)) {
    Ot_Migrate_Cli::error('DB resources not found in application.ini');
}

$dbConfig = array(
    'adapter'  => $applicationIni->resources->db->adapter,
    'username' => $applicationIni->resources->db->params->username,
    'password' => $applicationIni->resources->db->params->password,
    'host'     => $applicationIni->resources->db->params->host,
    'port'     => $applicationIni->resources->db->params->port,
    'dbname'   => $applicationIni->resources->db->params->dbname
);

$configXml = new Zend_Config_Xml($configFilePath . '/config.xml', 'production');
Zend_Registry::set('config', $configXml);

if (($opts->cmd == 'up' || $opts->cmd == 'down' || $opts->cmd == 'setlatestversion') && !isset($opts->version)) {
    Ot_Migrate_Cli::error('Version must be specified' . "\n\n" . $opts->getUsageMessage());
}

$tablePrefix = $configXml->app->tablePrefix;

try {
    $migration = new Ot_Migrate($dbConfig, $pathToMigrateFiles, $tablePrefix);
    $result = $migration->migrate($opts->cmd, $opts->version);
} catch (Exception $e) {
    Ot_Migrate_Cli::error($e->getMessage());
}

Ot_Migrate_Cli::status($result);

exit;