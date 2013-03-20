<?php
class Ot_Cron_Job
{
    protected $_key;
    protected $_name;
    protected $_description;
    protected $_schedule;
    protected $_jobClassname;

    public function __construct($key = '', $name = '', $description = '', $schedule = '', $classname = '')
    {
        $this->setKey($key)
             ->setName($name)
             ->setDescription($description)
             ->setSchedule($schedule)
             ->setJobClassname($classname);
    }

    public function setKey($_key)
    {
        $this->_key = $_key;
        return $this;
    }

    public function getKey()
    {
        return $this->_key;
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

    public function setJobClassname($_classname)
    {
        $this->_jobClassname = $_classname;
        return $this;
    }

    public function getJobClassname()
    {
        return $this->_jobClassname;
    }
    
    public function getJobObj()
    {
        $reflection = new ReflectionClass($this->_jobClassname);
        
        if (!$reflection->implementsInterface('Ot_Cron_JobInterface')) {
            throw new Exception('Invalid cron job type found.  Must implement Ot_Cron_JobInterface');
        }
        
        return new $this->_jobClassname;
    }
}