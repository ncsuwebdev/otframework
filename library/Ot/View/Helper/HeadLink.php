<?php

class Ot_View_Helper_HeadLink extends Zend_View_Helper_HeadLink
{
    protected $_appversion = '';

    public function __construct()
    {
        $this->_appversion = Ot_Application_Version::getVersion();

        parent::__construct();
    }

    public function prependStylesheet($item)
    {
        if (!preg_match('/^http/i', $item)) {
            $item .= '?v=' . $this->_appversion;
        }

        parent::prependStylesheet($item);
    }

    public function appendStylesheet($item)
    {
        if (!preg_match('/^http/i', $item)) {
            $item .= '?v=' . $this->_appversion;
        }

        parent::appendStylesheet($item);
    }
}