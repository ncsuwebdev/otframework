<?php
class Ot_Var_Type_Ranking extends Ot_Var_Abstract
{
    /**
     * The array of options returned for a custom attribute of type "ranking"
     *
     * @var array
     */
    protected $_rankingOptions = array(
        'N/A' => 'N/A',
        '1' => '1',
        '2' => '2', 
        '3' => '3', 
        '4' => '4', 
        '5' => '5',
    );
    
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Radio($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setDescription($this->getDescription());        
        $elm->setMultiOptions($this->_rankingOptions);
        $elm->setValue($this->getValue());
        $elm->setRequired($this->getRequired());
        $elm->setSeparator('');
        return $elm;
    }
    
    public function getDisplayValue() 
    {
        return (isset($this->_rankingOptions[$this->getValue()])) ?  $this->_rankingOptions[$this->getValue()] : 'N/A';
    }
}