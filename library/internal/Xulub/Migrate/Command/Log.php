<?php

class Xulub_Migrate_Command_Log extends Xulub_Migrate_Command_Abstract 
{
    private $_msg;
    
    public function __construct($msg)
    {
        $this->_msg = $msg;
    }
    
    public function execute()
    {
        $this->_write($this->_msg);
    }
}