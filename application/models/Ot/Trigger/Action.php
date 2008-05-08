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
 * @package    Ot_Trigger_Action
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 * @license    BSD License
 * @see        http://itdapps.ncsu.edu
 * @version    SVN: $Id: $
 */

/**
 * Model to interact with the action triggers
 *
 * @package    Ot_Trigger_Action
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of Information Technology
 *
 */
class Ot_Trigger_Action extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_trigger_action';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'triggerActionId';
    
    public function getActionsForTrigger($triggerId)
    {
    	$where = $this->getAdapter()->quoteInto('triggerId = ?', $triggerId);
    	
    	return $this->fetchAll($where);
    }
}

