<?php

class Ot_View_Helper_MaintenanceMode extends Zend_View_Helper_Abstract
{    
    public function maintenanceMode()
    {
        return $this;
    }
    
    public function header()
    {
        $html = array();
        
        $html[] = '<div class="maintenance maintenanceModeOn">';
        $html[] = 'Site is currently in maintenance and not available to general users.  ';
        $html[] = '<a href="' . $this->view->url(array('controller' => 'maintenance'), 'ot', true) . '">Click Here</a> to disable.';
        $html[] = '</div>';
        
        return join(PHP_EOL, $html);
    }
    
    public function publicLayout()
    {
        $html = array();
        
        $html[] = $this->view->doctype('XHTML1_STRICT');
        $html[] = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">';
        $html[] = '<head>';
        $html[] = '<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />';
        $html[] = '<title>' . $this->view->configVar('appTitle') . '</title>';
        $html[] = '</head>';
        $html[] = '<body>';
        $html[] = '<h2>This application is currently in maintenance mode.  It should be back online shortly.</h2>';
        $html[] = '<br /><br />';
        $html[] = '<a href="' . $this->view->url(array(), 'login', true) . '">Administrators may log in here.</a>';
        $html[] = '</body>';
        $html[] = '</html>';

        return join(PHP_EOL, $html);
    }
}