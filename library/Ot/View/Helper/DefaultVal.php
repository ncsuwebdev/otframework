<?php

/**
 * This view helper returns a value, or a default value if the value is empty
 *
 */
class Ot_View_Helper_DefaultVal extends Zend_View_Helper_Abstract
{   
    /**
     * Checks if the passed $val is empty or not.  If it is not,
     * it returns the $val.  If it is, it returns the translation
     * of $alt;
     *
     * @param string $val
     * @param string $alt
     * @return string
     */
    public function defaultVal($val, $alt)
    {
        if ($val != '') {
            return $val;
        }
        
        $translate = Zend_Registry::get('Zend_Translate');
        
        return $translate->translate($alt);
    }
}