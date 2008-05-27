<?php
class Ot_Db_Table extends Zend_Db_Table
{
    /**
     * Overwrites the update method to allow the second param ($where clause)
     * to be null, which would then generate the where clause with the values 
     * of the primary keys
     *
     * @param array $data
     * @param mixed $where
     * @return result from update
     */
    public function update(array $data, $where)
    {
        if (is_null($where)) {        
            foreach ($this->_primary as $key) {
                if (!is_null($where)) {
                    $where .= ' AND ';
                }
                
                if (!isset($data[$key])) {
                    throw new Ot_Exception_Input("Primary key $key not set");
                }
                
                $where .= $this->getAdapter()->quoteInto($key . ' = ?', $data[$key]);
            }
        }

        return parent::update($data, $where);
    }
    
    public function find()
    {
        $args = func_get_args();
        
        $result = parent::find($args);
        
        if (count($this->_primary) == 1) {
            if ($result instanceof Zend_Db_Table_Rowset && $result->count() == 1) {
                return $result->current();
            } elseif (is_array($result) && count($result) == 1) {
                return $result[0];
            } elseif (!is_array(func_get_arg(0))) {
                return null;
            }
        }
        
        return $result;
    }
}