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
 * @package    Ot_Bug
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with auth adapters
 *
 * @package    Ot_Bug
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of
 *             Information Technology
 *
 */
class Ot_Model_DbTable_AuthAdapter extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_auth_adapter';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'adapterKey';

    /**
     * Returns all the enabled adapters
     */
    public function getEnabledAdapters()
    {
        $where = $this->getAdapter()->quoteInto('enabled = ?', 1);
        return $this->fetchAll($where, 'displayOrder');
    }

    /**
     * Returns the number of enabled adapters
     */
    public function getNumberOfEnabledAdapters()
    {
        $enabledAdapters = $this->getEnabledAdapters();
        return $enabledAdapters->count();
    }

    /**
     * Updates the display order of the Adapters
     *
     * @param array $order
     */
    public function updateAdapterOrder($order)
    {
        $dba = $this->getAdapter();

        $dba->beginTransaction();

        $i = 1;
        foreach ($order as $o) {

            $data = array("displayOrder" => $i);

            $where = $dba->quoteInto('adapterKey = ?', $o);

            try {
                $this->update($data, $where);
            } catch(Exception $e) {
                $dba->rollBack();
                throw $e;
            }
            $i++;
        }

        $dba->commit();
    }
}