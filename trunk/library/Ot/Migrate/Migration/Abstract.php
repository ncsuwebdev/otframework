<?php
abstract class Ot_Migrate_Migration_Abstract
{
    protected $_options = array();
    
    public function __construct(array $options)
    {
        $this->_options = $options;
    }
    
    public function __get($key)
    {
        return isset($this->_options[$key]) ? $this->_options[$key] : null;
    }
    
    abstract public function up($dba);
    
    abstract public function down($dba);
}