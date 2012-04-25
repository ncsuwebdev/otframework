<?php
class Ot_TriggerPlugin
{
    protected $_pluginId;
    protected $_description;

    public function __construct($pluginId = '', $description = '')
    {
        $this->setPluginId($pluginId);
        $this->setDescription($description);
    }

    public function setPluginId($_pluginId)
    {
        $this->_pluginId = $_pluginId;
        return $this;
    }

    public function getPluginId()
    {
        return $this->_pluginId;
    }

    public function setDescription($_description)
    {
        $this->_description = $_description;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }
}