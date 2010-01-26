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
 * @package    Ot_Filter_Mac
 * @category   filter
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt  BSD License
 * @version    SVN: $Id: $
 */

/**
 * Filters mac address.
 *
 * @package    Ot_Filter_Mac
 * @category   Filter
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 */
class Ot_Filter_Mac implements Zend_Filter_Interface
{
    /**
     * delimiter between the numbers in the mac
     *
     * @var mixed
     */
    protected $_delimiter = ':';

    /**
     * Class constructor
     *
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * sets the delimiter
     *
     * @param mixed $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->_delimiter = ':';
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value, removing all but digit characters separated by $delimiter
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $matches = array();
        preg_match_all('/([0-9a-fA-F]{2})[^0-9^a-f^A-F]*/', $value, $matches);

        $macArray = $matches[1];

        return strtoupper(implode($this->_delimiter, $macArray));
    }
}
