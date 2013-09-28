<?php

class Xulub_Migrate_Command_Move extends Xulub_Migrate_Command_Abstract 
{
    private $_path;
    
    private $_newPath;
    
    public function __construct($old = '', $new = '')
    {
        $this->_path = $old;
        $this->_newPath = $new;
    }
    
    public function execute()
    {
        $command = $this->getScmPath() . ' mv ' . $this->_path . ' ' . $this->_newPath;
        $this->_exec($command);
    }
    
    public function getPath()
    {
        return $this->_path;
    }
    
    public function getNewPath()
    {
        return $this->_newPath;
    }
}
