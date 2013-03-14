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
 * @package    Ot_Custom
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    BSD License
 * @version    SVN: $Id: $
 */

/**
 * Model allows simple integration of custom attributes that are tied to parent
 * objects.
 *
 * @package    Ot_Custom
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */
class Ot_Model_Custom
{    
    
    /**
     * Converts options for selects and radios from a string to an array
     *
     * @param string $options
     * @return array
     */
    public function convertOptionsToArray($options)
    {
        $options = unserialize($options);
        
        if (!is_array($options)) {
            return array();            
        }
        
        $opt = array();
        foreach ($options as $key => $value) {
            $opt['option_' . $key] = $value;
        }
        
        return $opt;
    }
    
    /**
     * Converts options for selects and radios from an array to a string 
     * (used for storage in DB)
     *
     * @param array $options
     * @return string
     */
    public function convertOptionsToString($options)
    {
        return serialize((is_array($options)) ? $options: array());
    }
    
    /**
     * Renders an attribute using the form template
     *
     * @param array $attribute
     * @param mixed $value
     * @return resulting HTML
     */
    public function renderFormElement($attribute, $renderType, $value = null)
    {
        switch ($renderType) {
            case 'Zend_Form':
                return $this->_renderZendFormElement($attribute, $value);
            case 'HTML':
                return $this->_renderHtmlElement($attribute, $value);
            default:
                return '';
        }
    }
    
    protected function _renderZendFormElement($attribute, $value = null)
    {
        $opts = array();
        
        $name = 'custom_' . $attribute['attributeId'];
                
        switch ($attribute['type']) {
            
            case 'description':
                $elm = new Ot_Form_Element_Description($name);
                $opts = $this->convertOptionsToArray($attribute['options']);
                $elm->setDescription(isset($opts[0]) ? $opts[0] : '');
                break;
            case 'text':
                $elm = new Zend_Form_Element_Text($name);
                $elm->size = '20';
                break;
            case 'textarea':
                $elm = new Zend_Form_Element_Textarea($name);
                $elm->rows = '3';
                $elm->cols = '50';
                break;
            case 'radio':
                $elm = new Zend_Form_Element_Radio($name);
                $elm->addMultiOptions($this->convertOptionsToArray($attribute['options']));
                
                $listsep = "<br />\n";
                if ($attribute['direction'] == "horizontal") {
                    $listsep = "&nbsp;";
                }          

                $elm->setSeparator($listsep);                
                break;
            case 'checkbox':
                $elm = new Zend_Form_Element_Checkbox($name);
                break;
            case 'select':
                $elm = new Zend_Form_Element_Select($name);
                $elm->addMultiOptions($this->convertOptionsToArray($attribute['options']));
                break;
            case 'multicheckbox':
                $elm = new Zend_Form_Element_MultiCheckbox($name);
                $elm->addMultiOptions($this->convertOptionsToArray($attribute['options']));
                break;
            case 'multiselect':
                $elm = new Zend_Form_Element_Multiselect($name);
                $elm->addMultiOptions($this->convertOptionsToArray($attribute['options']));
                break;
            case 'ranking':
                $elm = new Zend_Form_Element_Radio($name);
                $elm->addMultiOptions($this->_rankingOptions);
                
                $listsep = "<br />\n";
                if ($attribute['direction'] == "horizontal") {
                    $listsep = "&nbsp;";
                }          

                $elm->setSeparator($listsep);                
                break;
            default:
                return '';
        }
        
        if (!is_null($value) && $value != '') {
            
            if (is_array($value)) {
                $elm->setValue(array_keys($value));
            
                
            } elseif ($attribute['type'] == 'description') {
                $elm->setValue(true);
            
              
            } else {
                $elm->setValue($value);
            }
        }
        
        $elm->setLabel($attribute['label'] . ":");
        
        if ($attribute['required']) {
            $elm->setRequired(true);
        }
        return $elm;        
    }
    
    protected function _renderHtmlElement($attribute, $value = null)
    {
        $opts = array();
                
        if ($attribute['required']) {
            $opts['class'] = 'required';
        }
        
        if (empty($value)) {
            $value = null;
        }
        
        $name = 'custom_' . $attribute['attributeId'];
        
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        
        $formField = '';
        
        switch ($attribute['type']) {
            
            case 'description':
                $formField = new Ot_Form_Element_Description($name);
                $formField->setDescription($attribute['options'][0]);
                $formField->setValue(true);
                $formField = $formField->render();
                break;
            case 'text':
                $opts['size'] = '20';
                $formField = $view->formText($name, $value, $opts);
                break;
            case 'textarea':
                $opts['rows'] = "3";
                $opts['cols'] = "50";
                $formField = $view->formTextarea($name, $value, $opts);
                break;
            case 'radio':
                $listsep = "<br />\n";
                if ($attribute['direction'] == "horizontal") {
                    $listsep = "&nbsp;";
                }
                $formField = $view->formRadio($name, $value, $opts, $attribute['options'], $listsep);
                break;
            case 'checkbox':
                $formField = $view->formCheckbox($name, $value, $opts);
                break;
            case 'select':
                $opts['size'] = '1';
                $formField = $view->formSelect($name, $value, $opts, $attribute['options']);
                break;
            case 'multicheckbox':
                $opts['size'] = '5';
                $formField = $view->formMultiCheckbox($name, $value, $opts, $attribute['options']);
                break;
            case 'multiselect':
                $opts['size'] = '5';
                $formField = $view->formMultiselect($name, $value, $opts, $attribute['options']);
                break;
            case 'ranking':
                $tmpOptions = $this->_rankingOptions;
                             
                $listsep = "<br />\n";
                if ($attribute['direction'] == "horizontal") {
                    $listsep = "&nbsp;";
                }
                       
                $formField = $view->formRadio($name, $value, $opts, $tmpOptions, $listsep);
                break;
            default:
                return '';
        }
        
        return $formField;
    }
    
    /**
     * Saves data from custom attributes that are tied to a parent object and ID
     *
     * @param mixed $objectId
     * @param mixed $parentId
     * @param array $data
     */
    public function saveData($objectId, $parentId, array $data) 
    {        
        $av = new Ot_Model_DbTable_CustomAttributeValue();
        $dba = $av->getAdapter();
        
        $inTransaction = false;
        
        try {
            $dba->beginTransaction();
        } catch (Exception $e) {
            $inTransaction = true;
        }
        
        foreach ($data as $key => $value) {
            if (is_array($value)) { 
                $value = serialize($value);
                
                foreach ($value as &$v) {
                    $v = str_replace('option_', '', $v);
                }
                
                unset($v);
            }
            
            if (preg_match('/option\_/', $value)) {
                $value = str_replace('option_', '', $value);
            }
            
            $d = array(
                'objectId'    => $objectId,
                'parentId'    => $parentId,
                'attributeId' => $key,
                'value'       => $value,
            );
            
            $where = $dba->quoteInto('objectId = ?', $objectId) . ' AND ' . 
                $dba->quoteInto('parentId = ?', $parentId) . ' AND ' . 
                $dba->quoteInto('attributeId = ?', $key);
                
            $result = $av->fetchAll($where);
            
            if ($result->count() != 0) {
                try {
                     $av->update($d, $where);
                } catch (Exception $e) {
                    if (!$inTransaction) {
                        $dba->rollBack();
                    }
                    throw $e;
                }
            } else {
                try {
                     $av->insert($d);
                } catch (Exception $e) {
                    if (!$inTransaction) {
                        $dba->rollBack();
                    }
                    throw $e;
                }               
            }
        }
        
        if (!$inTransaction) {
            $dba->commit();
        }
    }
    
    /**
     * given a object and parent ID, removes all custom attributes associated with it
     *
     * @param mixed $objectId
     * @param mixed $parentId
     */
    public function deleteData($objectId, $parentId)
    {
        $av = new Ot_Model_DbTable_CustomAttributeValue();
        $dba = $av->getAdapter();
        
        $where = $dba->quoteInto('objectId = ?', $objectId) . 
          ' AND ' . 
          $dba->quoteInto('parentId = ?', $parentId);
          
        $av->delete($where);
        
    }
    
    /**
     * Gets all submitted data based on a object and parent ID, can also
     * render that data in a display or form template
     *
     * @param mixed $objectId
     * @param mixed $parentId
     * @return array
     */
    public function getData($objectId, $parentId, $renderType = 'none', $getDescriptions = true)
    {
        $attributes = $this->getAttributesForObject($objectId);
        $nv = new Ot_Model_DbTable_CustomAttributeValue();
        
        $ret = array();

        foreach ($attributes as $a) {

            if ($getDescriptions == false) {
                if ($a['type'] == 'description') {
                    continue;
                }    
            }
            
            $dba = $nv->getAdapter();
            $where = $dba->quoteInto('objectId = ?', $objectId) . ' AND ' . 
                $dba->quoteInto('parentId = ?', $parentId) . ' AND ' . 
                $dba->quoteInto('attributeId = ?', $a['attributeId']);
                
            $sv = $nv->fetchAll($where);

            $value = '';
            
            if ($sv->count() == 1) {
                $value = $sv->current()->value;
            }
            
            $tempA = $a;
            
            $a['options'] = $this->convertOptionsToArray($a['options']);
            
            $displayValue = $value;
            
            switch ($a['type']) {
                case 'multicheckbox':
                case 'multiselect':
                    
                    if (!empty($value)) {
                    
                        $value = unserialize($value);
                        
                        $displayValue = array();
                        
                        foreach ($value as $key => $v) {
                            $displayValue[$key] = (isset($a['options']['option_' . $v])) ? $a['options']['option_' . $v] : $v;
                        }
                        
                        $value = $displayValue;
                    }
                    
                    break;
                case 'select':
                case 'radio':
                    $displayValue = (isset($a['options']['option_' . $value])) ? $a['options']['option_' . $value] : '';
                    $value = 'option_' . $value;
                    break;
            }
            
           if ($a['type'] == 'description') {
               $displayValue = isset($a['options']['option_']) ? $a['options']['option_0'] : '';    
           }
            
            $temp = array(
                'attribute'  => $a,
                'value'      => $displayValue,
                'formRender' => '',
            );
            
            $temp['formRender'] = $this->renderFormElement($tempA, $renderType, $value);
            $ret[] = $temp;
        }
        
        return $ret;
    }
    
    /**
     * Updates the display order of the URLs from a group.
     *
     * @param int $groupId
     * @param array $order
     */
    public function updateAttributeOrder($objectId, $order)
    {
        $attr = new Ot_Model_DbTable_CustomAttribute();
        
        $dba = $attr->getAdapter();
        
        $dba->beginTransaction();

        $i = 1;
        foreach ($order as $o) {

            if (!is_integer($o)) {                
                $dba->rollBack();
                throw new Ot_Exception_Input("New position was not an integer.");
            }

            $data = array("order" => $i);

            $where = $dba->quoteInto('attributeId = ?', $o) .
                     " AND " .
                     $dba->quoteInto('objectId = ?', $objectId);

            try {
                $attr->update($data, $where);
            } catch(Exception $e) {
                $dba->rollBack();
                throw $e;
            }
            $i++;
        }
        
        $dba->commit();
    }       
}