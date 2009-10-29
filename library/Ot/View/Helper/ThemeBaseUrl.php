<?php

/**
 * This view helper returns the baseUrl of the application
 *
 */
class Ot_View_Helper_ThemeBaseUrl extends Zend_View_Helper_Abstract
{   
    /**
     * Returns the base URL to the theme
     *
     * 
     */
    public function themeBaseUrl()
    {
        return Zend_Controller_Front::getInstance()->getBaseUrl() . '/' . $this->view->applicationThemePath;
    }
}