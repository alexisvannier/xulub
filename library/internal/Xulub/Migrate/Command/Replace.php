<?php

class Xulub_Migrate_Command_Replace extends Xulub_Migrate_Command_Abstract 
{
    protected $_path;
    
    private $_replacements = array();
    
    public function __construct($path = '')
    {
        $this->_path = $path;
    }
    
    public function addReplace($search, $replacement)
    {
        $replacement = array(
            'type'          => 'preg',
            'search'        => $search,
            'replacement'   => $replacement
        );
        $this->_replacements[] = $replacement;
    }
    
    public function addStrReplace($search, $replacement)
    {
        $replacement = array(
            'type'          => 'str_replace',
            'search'        => $search,
            'replacement'   => $replacement
        );
        $this->_replacements[] = $replacement;
    }
    
    public function execute()
    {
        if ($this->isEnabled()) {
            $content = file_get_contents($this->_path);
        }
        
        foreach($this->_replacements as $replacement)
        {
            $this->_write(
                '   * (' . $replacement['type'] .')' . $replacement['search'] 
                . ' par ' . $replacement['replacement'] . '.'
            );                
            if ($this->isEnabled()) {
                switch($replacement['type']) {
                    case 'str_replace' :
                        $content = str_replace(
                            $replacement['search'], 
                            $replacement['replacement'], 
                            $content
                        );
                        break;
                    case 'preg' :
                    default :                         
                        $content = preg_replace(
                            $replacement['search'], 
                            $replacement['replacement'],
                            $content
                        );
                        break;
                }
                file_put_contents($this->_path, $content);
            }
        }
        $this->_write('');
    }
    
    public function getPath()
    {
        return $this->_path;
    }
}