<?php

/**
 * This formats a phone number appropriately for display
 *
 */
class Zend_View_Helper_FormatPhone extends Zend_View_Helper_Abstract
{
    
    /**
     * Formats the phone number appropriately
     * 
     * 5554561234 becomes (555) 456-1234
     * 4561234 becomes 456-1234
     * 51234 becomes 5-1234
     *
     * @param string The phone number to format
     */
    public function formatPhone($phone)
    {
        if (empty($phone)) {
            return '';
        }
        
        if (!is_numeric($phone)) {
            return $phone;
        }
        
        $phone = preg_replace("/[^0-9]/", "", $phone);

        if (strlen($phone) == 5) {
            
            return preg_replace("/([0-9]{1})([0-9]{4})/", "$1-$2", $phone);
            
        } else if (strlen($phone) == 7) {
            
            return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
            
        } else if (strlen($phone) == 10) {

            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
            
        } else if (strlen($phone) == 11) {
            
            return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 ($2) $3-$4", $phone);
            
        } else if (strlen($phone) == 12) {
            
            return preg_replace("/([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 ($2) $3-$4", $phone);
            
        } else if (strlen($phone) == 13) {
            
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 ($2) $3-$4", $phone);
            
        } else if (strlen($phone) == 14) {
            
            return preg_replace("/([0-9]{4})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 ($2) $3-$4", $phone);
            
        } else {
        
            return $phone;
        }
    }
}