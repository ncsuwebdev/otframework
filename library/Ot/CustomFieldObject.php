<?php
class Ot_CustomFieldObject
{
    protected $_objectId;
    protected $_description;

    public function __construct($objectId = '', $description = '')
    {
        $this->setObjectId($objectId);
        $this->setDescription($description);
    }

    public function setObjectId($_objectId)
    {
        $this->_objectId = $_objectId;
        return $this;
    }

    public function getObjectId()
    {
        return $this->_objectId;
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