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

// A list of all possible destination environments
$possibleDestinations = array(
    'staging',
    'development',
    'nonproduction',
    'testing'
);


// Sets up the expected options
$opts = new Zend_Console_Getopt(
    array (
        'source|s=s'      => 'Source DB Environment (' . implode($possibleSources, ', ') . ')',
        'destination|d=s' => 'Destination DB Environment (' . implode($possibleDestinations, ', ') . ')',
    )
);

// Get all available options and does some validity checking
try {
    $opts->parse();
} catch (Exception $e) {   
    Ot_Cli_Output::error($opts->getUsageMessage());
}

if (!isset($opts->source) || !in_array($opts->source, $possibleSources)) {
    Ot_Cli_Output::error('Source not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

if (!isset($opts->destination) || !in_array($opts->destination, $possibleDestinations)) {
    Ot_Cli_Output::error('Destination not sepecified or not available' . "\n\n" . $opts->getUsageMessage());
}

if ($opts->source == $opts->destination) {
    Ot_Cli_Output::error('Source and Destination cannot be the same' . "\n\n" . $opts->getUsageMessage());
}

$source = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $opts->source);
$destination = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', $opts->destination);

if (!isset($source->resources->db)) {
    Ot_Cli_Output::error('No DB information found in source' . "\n\n" . $opts->getUsageMessage());
}

if (!isset($destination->resources->db)) {
    Ot_Cli_Output::error('No DB information found in destination' . "\n\n" . $opts->getUsageMessage());
}

if ($source->resources->db->adapter != 'PDO_MYSQL' || $destination->resources->db->adapter != 'PDO_MYSQL') {
    Ot_Cli_Output::error('Source or Destination is not MYSQL' . "\n\n" . $opts->getUsageMessage());
}

// Drops all the tables in the destination DB
$dropCommand = "mysqldump -u " . escapeshellarg($destination->resources->db->params->username)
         . " -p" . escapeshellarg($destination->resources->db->params->password)
         . " -h " . escapeshellarg($destination->resources->db->params->host)
         . " --add-drop-table --no-data"
         . " " . escapeshellarg($destination->resources->db->params->dbname)
         . " | " 
         . " grep ^DROP "
         . " | "
         . " mysql -u " . escapeshellarg($destination->resources->db->params->username)
         . " -p" . escapeshellarg($destination->resources->db->params->password)
         . " -h " . escapeshellarg($destination->resources->db->params->host)
         . " " . escapeshellarg($destination->resources->db->params->dbname)
         ;

$copyCommand = "mysqldump -u " . escapeshellarg($source->resources->db->params->username)
         . " -p" . escapeshellarg($source->resources->db->params->password)
         . " -h " . escapeshellarg($source->resources->db->params->host)
         . " " . escapeshellarg($source->resources->db->params->dbname)
         . " | " 
         . " mysql -u " . escapeshellarg($destination->resources->db->params->username)
         . " -p" . escapeshellarg($destination->resources->db->params->password)
         . " -h " . escapeshellarg($destination->resources->db->params->host)
         . " " . escapeshellarg($destination->resources->db->params->dbname)
         ;

system($dropCommand);
system($copyCommand);

Ot_Cli_Output::success('Copy Complete');

// Exit without any errors
exit;