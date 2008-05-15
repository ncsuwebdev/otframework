<?php
class Internal_Account_Plugin_Attributes implements Ot_Plugin_Interface
{
    protected $_name = 'tbl_account_attributes';
    
    public function addSubForm()
    {
        return $this->_getForm();
    }
    
    public function addProcess($data)
    {
        $dba = Zend_Registry::get('dbAdapter');
        $dba->insert($this->_name, $data);
    }
    
    public function editSubForm($id)
    {
        $data = $this->get($id);
        
        return $this->_getForm($data);
    }
    
    public function editProcess($data)
    {
        $dba = Zend_Registry::get('dbAdapter');
        
        $where = $dba->quoteInto('userId = ?', $data['userId']);
        
        $select = $dba->select();

        $select->from($this->_name)
               ->where('userId = ?', $data['userId']);

        $result = $dba->fetchAll($select);

        if (count($result) == 1) {
            $dba->update($this->_name, $data, $where);   
        } else {
            $dba->insert($this->_name, $data);
        }
    }
    
    public function deleteProcess($id)
    {
        $dba = Zend_Registry::get('dbAdapter');
        
        $where = $dba->quoteInto('userId = ?', $id);

        return $dba->delete($this->_name, $where);
    }
    
    public function get($id)
    {
        $dba = Zend_Registry::get('dbAdapter');
        
        $select = $dba->select();

        $select->from($this->_name)
               ->where('userId = ?', $id);

        $result = $dba->fetchAll($select);

        if (count($result) == 1) {
            $result = $result[0];
        } else {
            $result = array();
        }
        
        $form = $this->_getForm($result);
        
        $data = array();
        foreach ($form->getElements() as $e) {
            $data[$e->getName()] = $e->getValue();
        }
        
        return $data;
    }
    
    public function dispatch($data)
    {}
    
    protected function _getForm($data = array())
    {        
        $form = new Zend_Form_SubForm();
        
        // create zend form elements here
        
        return $form;       
    }
}