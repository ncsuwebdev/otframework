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
 * @package    Ot_Activeuser
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with active users.
 *
 * @package    Ot_Activeuser
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Activeuser extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_active_user';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'accountId';
    
    /**
     * Deletes users that haven't been active in the last x minutes.
     */
    public function purgeInactiveUsers()
    {
        $config = Zend_Registry::get('config');
        
        $time = time() - (60 * $config->user->minutesToKeepUserActivity->val);
        $where = $this->getAdapter()->quoteInto('dt < ?', $time);
        $this->delete($where);
    }
}    