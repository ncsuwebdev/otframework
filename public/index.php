<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Main Bootstrap file
 * @category   Bootstrap
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */


/**
 * If you are using the OT Framework manager to manage the verison of OTF this application is using,
 * you will need to include the following lines to create the local symlinks to the correct OTF version.
 */

require_once $_SERVER['FRAMEWORK_MANAGER_PATH'];
$fm = new FrameworkManager();

$fmKey = $fm->getKey('ot_tools');

/*
foreach ($fmKey->ot as $key => $value) {
    $path = realpath(dirname(__FILE__) . '/..') . '/' . $key;

    if (is_link($path)) {
        if (readlink($path) != $value) {
            unlink($path);
            exec('ln -s ' . $value . ' ' . $path);
        }           
    } else {
        exec('ln -s ' . $value . ' ' . $path);
    }
}
*/
/**
 * End symlink setup code
 */

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
    
// Define path to application directory
defined('OT_APPLICATION_PATH')
    || define('OT_APPLICATION_PATH', realpath(dirname(__FILE__) . '/../ot/application'));    

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

$paths = array(
    $fmKey->zf,
    realpath(APPLICATION_PATH . '/../library'),
    realpath(OT_APPLICATION_PATH . '/../library'),
    get_include_path()
);

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, $paths));

/** Zend_Application */
require_once 'Zend/Application.php';  

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV, 
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();    