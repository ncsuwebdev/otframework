<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file _LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Version
 * @category   Library
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Tells the user what version of the OT Framework it is running
 *
 * @package   Ot_Version
 * @category  Library
 * @copyright Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Version
{
    /**
     * Release Version
     *
     * @var string
     */
    const VERSION = '3.0.14';

    public function getVersions()
    {
        return array(
            'OTFramework'   => self::VERSION,
            'ZendFramework' => Zend_Version::VERSION,
        );
    }
}
