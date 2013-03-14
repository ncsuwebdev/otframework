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
 * @package    Ot_Custom_Attribute
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model to store custom attribute objects for objects
 *
 * @package    Ot_Custom_Attribute
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_DbTable_CustomAttribute extends Ot_Db_Table
{
    /**
     * Name of the table in the database
     *
     * @var string
     */
    protected $_name = 'tbl_ot_custom_attribute';

    /**
     * Primary key of the table
     *
     * @var string
     */
    protected $_primary = 'attributeId';
    
    public function get($attributeId)
    {
        $thisAttribute = $this->find($attributeId);

        if (is_null($thisAttribute)) {
            throw new Ot_Exception_Data('msg-error-noAttribute');
        }
        
        $thisAttribute = $thisAttribute->toArray();
        
        $ftr = new Ot_CustomAttribute_FieldTypeRegister();
        
        $thisAttribute['fieldType'] = $ftr->getFieldType($thisAttribute['fieldTypeKey']);
        
        if (is_null($thisAttribute['fieldType'])) {
            throw new Ot_Exception_Data('Field type (' . $thisAttribute['fieldTypeKey'] . ' not registered');
        }
        
        $cahr = new Ot_CustomAttribute_HostRegister();
        
        $thisAttribute['host'] = $cahr->getHost($thisAttribute['hostKey']);
        
        if (is_null($thisAttribute['host'])) {
            throw new Ot_Exception_Data('Host (' . $thisAttribute['hostKey'] . ') not registered');
        }
        
        $options = unserialize($thisAttribute['options']);
        
        $thisAttribute['options'] = array();
        
        if (is_array($options)) {
            foreach ($options as $a) {
                $thisAttribute['options'][]['option'] = $a;
            }
        }
        
        return $thisAttribute;
    }
}