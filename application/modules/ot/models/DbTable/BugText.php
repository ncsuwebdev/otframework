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
 * @package    Ot_Bug_Text
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to do deal with bug reports
 *
 * @package    Ot_Bug_Text
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_DbTable_BugText extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_bug_text';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'bugTextId';

    /**
     * Gets all the bugs, with options to only show new bugs
     *
     * @param boolean $newOnly
     * @return result from fetchAll
     */
    public function getBugText($bugId, $order = 'ASC')
    {
        $where = $this->getAdapter()->quoteInto('bugId = ?', $bugId);

        return parent::fetchAll($where, 'postDt ' . $order);
    }
}