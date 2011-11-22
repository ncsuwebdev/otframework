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
 * @package    Ot_Log
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do all interaction for logs. Logs are entered into the database using
 * Zend_Log with a database adapter. Stored in the logs are priority, message,
 * userId, subscriptionId, and timestamp. We want to extract these logs for the
 * purpose of tracking a users history. Logs are not entered, updated, or deleted
 * through this model, it is simple for read-only purposes.
 *
 * @package    Ot_Log
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_DbTable_Log extends Ot_Db_Table
{
    /**
     * Database table name
     *
     * @var string
     */
    protected $_name = 'tbl_ot_log';

    /**
     * Primary key for the database
     *
     * @var string
     */
    protected $_primary = 'logId';

    /**
     * Gets all log entries based on an attribute name and ID
     *
     * @param string attributeName
     * @param int attributeId
     * @return Zend_Db_Table_Rowset
     */
    public function getLogByAttributeId($attributeName, $attributeId)
    {
        $dba = $this->getAdapter();

        $where = $dba->quoteInto('attributeName = ?', $attributeName) .
            ' AND ' .
            $dba->quoteInto('attributeId = ?', $attributeId);

        $order = 'timestamp ASC';

        return $this->fetchAll($where, $order);
    }
}
