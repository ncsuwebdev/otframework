<?php
class Ot_Form_Backup extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_tables = array();
    
    public function setTables(array $tables)
    {
        $this->_tables = $tables;
    }
    
    public function init()
    {
        $this->setAttrib('id', 'backup');
                  
        $tableName = $this->createElement('select', 'tableName', array('label' => 'Select A Table:'));
        $tableName->setRequired(true)
                  ->setMultiOptions($this->_tables);
        
        $format = $this->createElement('select', 'format', array('label' => 'Download Format'));
        $format->setRequired(true)
               ->addMultiOption('csv', 'CSV File');
        
        if (!is_null(`mysqldump`)) {
            $format->addMultiOption('sql', 'SQL Dump File');
        }
        
        $this->addElements(array($tableName, $format));
       
        $this->addElement('submit', 'submit', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'label'      => 'Download Table Data'
        ));      
               
        $this->addDisplayGroup(
            array('submit'),
            'actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions')
            )
        );
        
        return $this;

    }
}
