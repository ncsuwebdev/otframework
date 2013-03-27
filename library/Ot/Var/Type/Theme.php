<?php
class Ot_Var_Type_Theme extends Ot_Var_Abstract
{
    public function renderFormElement()
    {
        $elm = new Zend_Form_Element_Select($this->getName(), array('label' => $this->getLabel() . ':'));
        $elm->setRequired($this->getRequired());
        
        $tr = new Ot_Layout_ThemeRegister();
        
        $themes = $tr->getThemes();
        
        foreach ($themes as $t) {
            $elm->addMultiOption($t->getName(), $t->getLabel() . ' - ' . $t->getDescription());
        }
        
        $elm->setDescription($this->getDescription());
        $elm->setValue($this->getValue());
        return $elm;
    }
}