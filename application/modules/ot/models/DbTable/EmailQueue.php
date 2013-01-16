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
 * @package    Ot_Email_Queue
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do all email queue interaction
 *
 * @package    Ot_Email_Queue
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Information Technology Division
 *
 */
class Ot_Model_DbTable_EmailQueue extends Ot_Db_Table
{

    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_email_queue';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'queueId';

    /**
     * Queues an email in the system
     *
     * @param array $data
     * @return results from insert
     */
    public function queueEmail($data)
    {

        $data['queueDt']        = time();
        $data['zendMailObject'] = serialize($data['zendMailObject']);
        $data['status']         = 'waiting';
        $data['sentDt']         = 0;
        
        return parent::insert($data);
    }

    /**
     * Updates the table
     *
     * @param array $data The column=>value paired array of data
     * @param string $where The sql where clause to use
     * @return Result from Zend_Db_Table::update()
     */
    public function update(array $data, $where)
    {
        if (isset($data['zendMailObject'])) {
            $data['zendMailObject'] = serialize($data['zendMailObject']);
        }

        return parent::update($data, $where);
    }

    /**
     * Fetch all attributes matching $where
     *
     * @param string|array $where  OPTIONAL An SQL WHERE clause.
     * @param string|array $order  OPTIONAL An SQL ORDER clause.
     * @param int          $count  OPTIONAL An SQL LIMIT count.
     * @param int          $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset The row results per the Zend_Db_Adapter_Abstract fetch mode.
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $result = parent::fetchAll($where, $order, $count, $offset)->toArray();     

        foreach ($result as &$r) {
            $r['zendMailObject'] = unserialize($r['zendMailObject']);
        }

        return $result;
    }

    /**
     * Fetches one row.
     *
     * Honors the Zend_Db_Adapter_Abstract fetch mode.
     *
     * @param string|array $where OPTIONAL An SQL WHERE clause.
     * @param string|array $order OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row The row results per the Zend_Db_Adapter_Abstract fetch mode.
     */
    public function fetchRow($where = null, $order = null, $offset = null)
    {
        $result = parent::fetchRow($where, $order, $offset)->toArray();

        $result['zendMailObject'] = unserialize($result['zendMailObject']);

        return $result;
    }

    /**
     * Gets all email entries based on an attribute name and ID
     *
     * @param string attributeName
     * @param int attributeId
     * @return Zend_Db_Table_Rowset
     */
    public function getEmailsByAttributeId($attributeName, $attributeId)
    {
        $dba = $this->getAdapter();

        $where = $dba->quoteInto('attributeName = ?', $attributeName) .
            ' AND ' .
            $dba->quoteInto('attributeId = ?', $attributeId);

        $order = 'queueDt ASC';

        return $this->fetchAll($where, $order);
    }

    /**
     * Gets all emails that are waiting in the queue
     *
     * @return results from fetchAll
     */
    public function getWaitingEmails()
    {
        $dba = $this->getAdapter();

        $where = $dba->quoteInto('status = ?', 'waiting');

        return $this->fetchAll($where);
    }

    /**
     * Sends all emails that are waiting in the queue
     *
     * @return boolean
     */
    public function sendWaitingEmails()
    {
        $dba = $this->getAdapter();

        $where = $dba->quoteInto('status = ?', 'waiting');

        $queue = $this->fetchAll($where);

        foreach ($queue as $q) {
            try {
                $q['zendMailObject']->send();

                $q['status'] = 'sent';
                $q['sentDt'] = time();
            } catch (Exception $e) {
                $q['status'] = 'error';
                $q['sentDt'] = 0;
            }

            $where = $dba->quoteInto('queueId = ?', $q['queueId']);

            return $this->update($q, $where);
        }
    }
}