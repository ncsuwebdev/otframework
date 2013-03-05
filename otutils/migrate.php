<?php
ini_set('memory_limit', '-1');

// Define path to application directory
if (!defined('APPLICATION_PATH')) {
    $basepath = realpath(dirname(__FILE__));
    
    $applicationPath = $basepath . '/../application';
    
    if (preg_match('/vendor/i', $basepath)) {
        $applicationPath = $basepath . '/../../../../application';
    }
    
    define('APPLICATION_PATH', realpath($applicationPath));
}

require_once realpath(APPLICATION_PATH . '/../vendor/autoload.php');

$paths = array(
        realpath(APPLICATION_PATH . '/../library'),
        get_include_path(),
);

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, $paths));

// Path to where the database migration files live
$pathToMigrateFiles = realpath(APPLICATION_PATH . '/../db');


// A list of all possible environments the user can specify
$possibleEnvironments = array(
    'production',
    'staging',
    'development',
    'nonproduction',
    'testing'
);

// A list of all possible commands the user can give the migrator
$possibleCommands = array(
    'up',
    'down',
    'latest',
    'rebuild',
    'createtable',
    'setlatestversion'
);

// Sets up the expected options
$opts = new Zend_Console_Getopt(
    array (
        'cmd|c=s'         => 'Command to execute (' . implode($possibleCommands, ', ') . ')',
        'environment|e=s' => 'Environment to migrate (' . implode($possibleEnvironments, ', ') . ')',
        'version|v=s'     => 'Version to migrate to'
    )
);

// Get all available options and does some validity checking
try {
    $opts->parse();
} catch (Exception $e) {
    Ot_Cli_Output::error($opts->getUsageMessage());
}

if (!isset($opts->environment) || !in_array($opts->environment, $possibleEnvironments)) {
    Ot_Cli_Output::error('Environment not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

if (!isset($opts->cmd) || !in_array($opts->cmd, $possibleCommands)) {
    Ot_Cli_Output::error('Command not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', $opts->environment);

// Create application, bootstrap, and run
$application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap('db');
    

// Get the database configs
$tablePrefix = $application->getOption('tablePrefix');
$dbAdapter = $application->getBootstrap()->getPluginResource('db')->getDbAdapter();

// Run the migrator
try {
    $migration = new Ot_Migrate($dbAdapter, $pathToMigrateFiles, $tablePrefix);
    $result = $migration->migrate($opts->cmd, $opts->version);
} catch (Exception $e) {
    Ot_Cli_Output::error($e->getMessage());
}

// Display the results
Ot_Cli_Output::success($result);

// Exit without any errors
exit;