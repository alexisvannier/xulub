<?php

class Xulub_Migrate_Command_Delete extends Xulub_Migrate_Command_Abstract 
{
    private $_path;
    
    public function __construct($path = '')
    {
        $this->_path = $path;
    }
    
    public function execute()
    {
        $command = 'rm -Rf ' . $this->_path;
        $this->_exec($command);
    }
    
     public function getPath()
    {
        return $this->_path;
    }
}