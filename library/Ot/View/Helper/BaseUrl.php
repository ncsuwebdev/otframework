<?php

/**
 * This view helper returns the baseUrl of the application
 *
 */
class Zend_View_Helper_BaseUrl extends Zend_View_Helper_Abstract
{   
    /**
     * Takes any number of arguments and echoes the value in the array of values
     * at the current index.  The values are set the first time the function
     * is called.
     *
     * @param mixed Any number of arguments to use as cycle values
     */
    public function baseUrl()
    {
        return Zend_Controller_Front::getInstance()->getBaseUrl();
    }
}