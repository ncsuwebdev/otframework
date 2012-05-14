<?php

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

$paths = array(
        realpath(APPLICATION_PATH . '/../library'),
        get_include_path(),
);

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, $paths));

/** Zend_Application */
require_once 'Zend/Application.php';

function startApplication($environment = 'production') {

    // Define application environment
    defined('APPLICATION_ENV') || define('APPLICATION_ENV', $environment);
    
    ini_set('memory_limit', '-1');
    
    // Create application, bootstrap, and run
    $application = new Zend_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/application.ini'
    );
    
    $application->bootstrap('db');
    
    return $application;

}