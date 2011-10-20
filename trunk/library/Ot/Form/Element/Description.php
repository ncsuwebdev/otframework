<?php
class Ot_Form_Element_Description extends Zend_Form_Element
{
    public function render(Zend_View_Interface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }

        $content = '<div class="formDescriptionElement">' . $this->getDescription() . '</div>';
        
        return $content;
    }
}
