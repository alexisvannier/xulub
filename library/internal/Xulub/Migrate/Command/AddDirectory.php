<?php

class Xulub_Migrate_Command_AddDirectory extends Xulub_Migrate_Command_Abstract 
{
    private $_path;
    
    public function __construct($path = '')
    {
        $this->_path = $path;
    }
    
    public function execute()
    {
        /**
         * @todo : g�rer les droits sur les r�pertoires
         */
        $command = 'mkdir -p ' . $this->_path;
        $this->_exec($command);
        $command = $this->getScmPath() . ' add ' . $this->_path ;
        
        // ajout du --quiet pour �viter les warnings svn
        // car en version 1.4, on ne peut pas utiliser le --parents qui n'existe pas
        if ($this->_getScmType() == Xulub_Migrate_Command_Abstract::SCM_TYPE_SVN) {
            $command .= ' --quiet';
        }
        $this->_exec($command);
    }
    
    public function getPath()
    {
        return $this->_path;
    }
}