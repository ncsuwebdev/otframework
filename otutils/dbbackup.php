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

// A list of all possible source environments
$possibleSources = array(
    'production',
    'staging',
    'development',
    'nonproduction',
    'testing'
);

// Sets up the expected options
$opts = new Zend_Console_Getopt(
    array (
        'environment|e=s' => 'DB Environment (' . implode($possibleSources, ', ') . ')',
        'path|p=s' => 'Absolute file path to write the file to.',
    )
);

// Get all available options and does some validity checking
try {
    $opts->parse();
} catch (Exception $e) {   
    Ot_Cli_Output::error($opts->getUsageMessage());
}

if (!isset($opts->environment) || !in_array($opts->environment, $possibleSources)) {
    Ot_Cli_Output::error('Environment not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

if (!isset($opts->path)) {
    Ot_Cli_Output::error('Path not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

if (!is_writable($opts->path)) {
    Ot_Cli_Output::error('Path is not writable' . "\n\n" . $opts->getUsageMessage());
}

$ini = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $opts->environment);

if (!isset($ini->resources->db)) {
    Ot_Cli_Output::error('No DB information found in environment' . "\n\n" . $opts->getUsageMessage());
}

if ($ini->resources->db->adapter != 'PDO_MYSQL') {
    Ot_Cli_Output::error('DB is not MYSQL' . "\n\n" . $opts->getUsageMessage());
}

date_default_timezone_set((isset($ini->phpSettings->date->timezone)) ? $ini->phpSettings->date->timezone : 'America/New_York');

$filename = preg_replace('/\/$/', '', $opts->path) . '/' . preg_replace('/[^a-z0-9]+/i', '_', $ini->resources->db->params->host . ':' . $ini->resources->db->params->dbname . ':' . date('r')) . '.sql';

// Drops all the tables in the destination DB
$dumpCommand = "mysqldump -u " . escapeshellarg($ini->resources->db->params->username)
         . " -p" . escapeshellarg($ini->resources->db->params->password)
         . " -h " . escapeshellarg($ini->resources->db->params->host)         
         . " " . escapeshellarg($ini->resources->db->params->dbname)
         . " > " . $filename
         ;

system($dumpCommand);

Ot_Cli_Output::success('Backup Complete');

// Exit without any errors
exit;