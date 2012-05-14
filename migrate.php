<?php

require_once 'utils/Config.php';
require_once 'Zend/Console/Getopt.php';

// Path to where the database migration files live
$pathToMigrateFiles = dirname(__FILE__) . '/db';

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
    Ot_Migrate_Cli::error($opts->getUsageMessage());
}

if (!isset($opts->environment) || !in_array($opts->environment, $possibleEnvironments)) {
    Ot_Migrate_Cli::error('Environment not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

if (!isset($opts->cmd) || !in_array($opts->cmd, $possibleCommands)) {
    Ot_Migrate_Cli::error('Command not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

// Start the application
$application = startApplication($opts->environment);

// Get the database configs
$tablePrefix = $application->getOption('tablePrefix');
$dbAdapter = $application->getBootstrap()->getPluginResource('db')->getDbAdapter();

// Run the migrator
try {
    $migration = new Ot_Migrate($dbAdapter, $pathToMigrateFiles, $tablePrefix);
    $result = $migration->migrate($opts->cmd, $opts->version);
} catch (Exception $e) {
    Ot_Migrate_Cli::error($e->getMessage());
}

// Display the results
Ot_Migrate_Cli::status($result);

// Exit without any errors
exit;