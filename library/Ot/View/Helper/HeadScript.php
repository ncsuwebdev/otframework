<?php

class Ot_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    protected $_appversion = '';

    public function __construct()
    {
        $this->_appversion = Ot_Application_Version::getVersion();

        parent::__construct();
    }

    public function prependFile($item)
    {
        if (!preg_match('/^http/i', $item)) {
            $item .= '?v=' . $this->_appversion;
        }

        parent::prependFile($item);
    }

    public function appendFile($item)
    {
        if (!preg_match('/^http/i', $item)) {
            $item .= '?v=' . $this->_appversion;
        }

        parent::appendFile($item);
    }
}