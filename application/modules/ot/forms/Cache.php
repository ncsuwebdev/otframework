<?php
class Ot_Form_Cache extends Twitter_Bootstrap_Form_Vertical
{
    public function init()
    {
        $this->setAttrib('id', 'clearCache');
        
        $this->addElement('submit', 'clearCache', array(
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_WARNING,
            'label'      => 'ot-cache-index:linkClear',
        ));       

        return $this;
    }        
}
