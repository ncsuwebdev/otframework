<?php
class Ot_CustomAttribute_Host
{
    protected $_key;
    protected $_name;
    protected $_description;

    public function __construct($key = '', $name = '', $description = '')
    {
        $this->setKey($key);
        $this->setName($name);
        $this->setDescription($description);
    }

    public function setName($_name)
    {
        $this->_name = $_name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setKey($_key)
    {
        $this->_key = $_key;
        return $this;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function setDescription($_description)
    {
        $this->_description = $_description;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }
    
    public function getAttributes()
    {
        $attr = new Ot_Model_DbTable_CustomAttribute();
        
        $where = $attr->getAdapter()->quoteInto('hostKey = ?', $this->getKey());
        
        $results = $attr->fetchAll($where, 'order')->toArray();
        
        $attributes = array();
        
        $ftr = new Ot_CustomAttribute_FieldTypeRegister();
        
        $fieldTypes = $ftr->getFieldTypes();
        
        foreach ($results as $r) {
            if (!isset($fieldTypes[$r['fieldTypeKey']])) {
                continue; 
            }
            
            $r['fieldType'] = $fieldTypes[$r['fieldTypeKey']];
            $r['var'] = $r['fieldType']->getVar();
            $r['var']->setLabel($r['label'])
                     ->setDescription($r['description'])
                     ->setOptions(unserialize($r['options']))
                     ->setName($this->getKey() . '-' . $r['attributeId'])
                     ;
            
            $attributes[] = $r;
        }
        
        return $attributes;
    }
}