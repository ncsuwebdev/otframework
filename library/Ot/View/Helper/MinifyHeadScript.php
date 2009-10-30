<?php

/**
 * Minifies the javascript files added via the minifyHeadScript helper using 
 * minify (http://code.google.com/p/minify/)
 *
 */
class Ot_View_Helper_MinifyHeadScript extends Zend_View_Helper_HeadScript
{
    
    protected $_regKey = 'Ot_View_Helper_MinifyHeadScript';
    
    public function minifyHeadScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
    {
        return parent::headScript($mode, $spec, $placement, $attrs, $type);
    }
    
    public function toString()
    {
        $items = array();
        $scripts = array();
        $baseUrl = $this->getBaseUrl();
        
        // we can only support files
        foreach ($this as $item) {
            if (isset($item->attributes['src']) && !empty($item->attributes['src'])) {
                $scripts[] = str_replace($baseUrl, '', $item->attributes['src']);
            }
        }
        
        //remove the slash at the beginning if there is one
        if (substr($baseUrl, 0, 1) == '/') {
            $baseUrl = substr($baseUrl, 1);
        }
        
        $item = new stdClass();
        $item->type = 'text/javascript';
        $item->attributes['src'] = $this->getMinUrl() . '?b=' . $baseUrl . '&f=' . implode(',', $scripts);
        $scriptTag = $this->itemToString($item, '', '', '');
        
        return $scriptTag;
    }
    
    public function getMinUrl() {
        return $this->getBaseUrl() . '/min/';
    }
    
    public function getBaseUrl(){
        return Zend_Controller_Front::getInstance()->getBaseUrl();
    }
}