<?php

class Xulub_Migrate_Command_Add extends Xulub_Migrate_Command_Abstract 
{
    private $_path;
    
    private $_content;
    
    public function __construct($path = '', $content = '')
    {
        $this->_path = $path;
        $this->_content = $content;
    }
    
    public function execute()
    {
        $this->_write(' - création du fichier : ' . $this->_path);
        if ($this->isEnabled())
        {
            if (!is_dir(dirname($this->_path)))
            {
                $command = 'mkdir -p ' . dirname($this->_path);
                $this->_exec($command);
            }
            file_put_contents($this->_path, $this->_content);
            $command = $this->getScmPath() . ' add ' . $this->_path;
            $this->_exec($command);            
        }
    }
    
    public function getPath()
    {
        return $this->_path;
    }
}