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
 * Remove this if the OT framework is hosted locally from within the application.
 * 
 * Change the version number to change framework versions
 */
// Uncomment this and fill in the $otVersion for new applications that do not have the framework hosted locally
/* 
	$otVersion = '2.0.9';
	
	$paths = array(
		'./ot'        => $_SERVER['SHARED_LIB_PATH'] . '/OT/' . $otVersion . '/ot',
		'./public/ot' => $_SERVER['SHARED_LIB_PATH'] . '/OT/' . $otVersion . '/public/ot',
	);
	
	foreach ($paths as $key => $value) {
		if (is_link($key)) {
			if (readlink($key) != $value) {
				unlink($key);
				exec('ln -s ' . $value . ' ' . $key);
			}			
		} else {
			exec('ln -s ' . $value . ' ' . $key);
		}
	}
*/
/**
 * End symlink setup code
 */

$includePaths = array();

/**
 * Add any additional include paths here.  If you host Zend Framework outside the 
 * standard /library or /ot/library, you will want to include the path to
 * the library here.
 */
$includePaths[] = $_SERVER['SHARED_LIB_PATH'] . '/Zend/Framework/1.7.8/';
	
require_once './ot/library/Ot/Bootstrap.php';
$bs = Ot_Bootstrap::getInstance($includePaths);           

/**
 * Custom data based on the specific application requirements
 */
require_once $_SERVER['KEY_MANAGER2_PATH'];
$km = new KeyManager;

$key = $km->getKey('ot_sandbox');

$dbConfig = array(
    'adapter'  => 'PDO_MYSQL',
    'username' => $key->username,
    'password' => $key->password,
    'host'     => $key->host,
    'port'     => $key->port,
    'dbname'   => $key->dbname
    );
    
/**
 * End Custom Data
 */

// Run this if you would like to enable caching
//$bs->enableCaching();

// Run this to set the front controller to throw exceptions
$bs->throwExceptions();

// Dispatch the request
$bs->dispatch($dbConfig);