<?php
class Ot_Cron
{
    protected $_name;
    protected $_description;
    protected $_schedule;
    protected $_method;

    public function __construct($name = '', $description = '', $schedule = '')
    {
        $this->setName($name)
             ->setDescription($description)
             ->setSchedule($schedule);
    }

    public function setName($_name)
    {
        $this->_name = $_name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
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

    public function setSchedule($_schedule)
    {
        $this->_schedule = $_schedule;
        return $this;
    }

    public function getSchedule()
    {
        return $this->_schedule;
    }

    public function setMethod(Ot_Cron_JobInterface $_method)
    {
        $this->_method = $_method;
        return $this;
    }

    public function getMethod()
    {
        return $this->_method;
    }
}