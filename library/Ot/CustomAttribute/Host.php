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
    
    public function getAttributes($hostParentId = null)
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
                     ->setName($this->getKey() . $r['attributeId'])
                     ->setRequired($r['required'])
                     ;
            
            $attributes[$this->getKey() . $r['attributeId']] = $r;
        }
        
        if (!is_null($hostParentId)) {
            
            $attrValModel = new Ot_Model_DbTable_CustomAttributeValue();
            
            $where = $attrValModel->getAdapter()->quoteInto('hostKey = ?', $this->getKey())
                   . ' AND '
                   . $attrValModel->getAdapter()->quoteInto('hostParentId = ?', $hostParentId)
                   ;
            
            $attrValues = $attrValModel->fetchAll($where);
            
            foreach ($attrValues as $a) {
                if (isset($attributes[$this->getKey() . $a->attributeId])) {
                    $attributes[$this->getKey() . $a->attributeId]['var']->setRawValue($a->value);
                }
            }            
        }
        
        return $attributes;
    }
    
    public function saveAttribute(Ot_Var_Abstract $var, $hostParentId, $attributeId)
    {
        $model = new Ot_Model_DbTable_CustomAttributeValue();

        $where = $model->getAdapter()->quoteInto('hostParentId = ?', $hostParentId)
                . ' AND '
                . $model->getAdapter()->quoteInto('hostKey = ?', $this->getKey())
                . ' AND '
                . $model->getAdapter()->quoteInto('attributeId = ?', $attributeId)
                ;
        
        
        $thisVar = $model->fetchAll($where);                

        $data = array(
            'hostParentId' => $hostParentId, 
            'hostKey'      => $this->getKey(),
            'attributeId'  => $attributeId,
            'value'        => $var->getRawValue(),
        );

        if ($thisVar->count() == 0) {
            $model->insert($data);
        } else {
            $model->update($data, $where);
        }        
    }
    
    public function delete($hostParentId)
    {
        $model = new Ot_Model_DbTable_CustomAttributeValue();

        $where = $model->getAdapter()->quoteInto('hostParentId = ?', $hostParentId)
                . ' AND '
                . $model->getAdapter()->quoteInto('hostKey = ?', $this->getKey())
                ;
        
        $model->delete($where);
    }
}