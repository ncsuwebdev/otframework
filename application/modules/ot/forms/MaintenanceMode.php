<?php
class Ot_Form_MaintenanceMode extends Twitter_Bootstrap_Form_Vertical
{
    protected $_currentMaintenanceModeStatus = false;
    
    public function setCurrentMaintenanceModeStatus($status)
    {
        $this->_currentMaintenanceModeStatus = $status;
    }
    public function init()
    {
        $this->setAttrib('id', 'maintenanceMode');

        $status = $this->createElement('hidden', 'status');
        $status->setValue(($this->_currentMaintenanceModeStatus) ? 0 : 1);
        
        $this->addElement($status);
        
        $this->addElement('submit', 'submit', array(
            'buttonType' => ($this->_currentMaintenanceModeStatus) ? Twitter_Bootstrap_Form_Element_Submit::BUTTON_DANGER : Twitter_Bootstrap_Form_Element_Submit::BUTTON_SUCCESS,
            'label'      => ($this->_currentMaintenanceModeStatus) ? 'ot-maintenance-index:turnOff' : 'ot-maintenance-index:turnOn',
        ));       

        return $this;
    }
    
    
}
