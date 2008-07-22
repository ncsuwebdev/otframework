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

require_once './ot/library/Ot/Bootstrap.php';

$configFiles = array(
    'acl'              => './config/acl.xml',
    'api'              => './config/api.xml',
    'appPhp'           => './config/app.php',
    'app'              => './config/app.xml',
    'nav'              => './config/nav.xml',
    'textSubstitution' => './config/textSubstitution.xml',
    'user'             => './config/user.xml',
    'trigger'          => './config/trigger.xml',
    'custom'           => './config/custom.xml',
);

$bs = new Ot_Bootstrap('http', $configFiles, 'production');

$bs->dispatch();