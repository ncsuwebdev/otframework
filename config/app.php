<?php
/**
 * 
 *
 * This file does any setup needed for the application to run.  This file is called
 * first thing from the bootstrap before any files are included or connections to
 * datasources made.
 *
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
 * @package    
 * @category   Configuration File
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @author     Jason Austin <jason_austin@ncsu.edu>
 * @author     Garrison Locke <garrison_locke@ncsu.edu>
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: appConfig.php 141 2007-07-11 18:26:09Z jfaustin@EOS.NCSU.EDU $
 */

/**
 * Include our key manager software for centralized key management
 */
require_once $_SERVER['KEY_MANAGER_PATH'];
set_include_path('.'
                  . PATH_SEPARATOR . $_SERVER['SHARED_LIB_PATH'] . '/Zend/Framework/1.5.1/'
                  . PATH_SEPARATOR . $_SERVER['SHARED_LIB_PATH'] . '/Smarty/2.6.18/'
                  . PATH_SEPARATOR . get_include_path());

$km = new KeyManager;

$key = $km->getKeyObject('ot_sandbox');

$dbConfig = array(
    'adapter'  => 'PDO_MYSQL',
    'username' => $key->user,
    'password' => $key->password,
    'host'     => $key->host,
    'port'     => $key->port,
    'dbname'   => $key->dbname
    );
