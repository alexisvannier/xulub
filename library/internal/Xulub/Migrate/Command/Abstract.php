<?php
abstract class Xulub_Migrate_Command_Abstract 
{
    const SCM_TYPE_GIT = 'git';
    const SCM_TYPE_SVN = 'svn';
    const SCM_TYPE_UNKNOWN = 'inconnu';
    
    private $_isTest = true;
    
    private $_scmPath;
    
    private $_scmType;
    
    /**
     * Retourne le type de SCM utilisé
     * return string
     */
    protected function _getScmType() {
        if (strstr($this->_scmPath, self::SCM_TYPE_GIT) !== false) {
            $this->_scmType = self::SCM_TYPE_GIT;
        } else if (strstr($this->_scmPath, self::SCM_TYPE_SVN) !== false) {
            $this->_scmType = self::SCM_TYPE_SVN;
        } else {
            $this->_scmType = self::SCM_TYPE_UNKNOWN;
        }
        return $this->_scmType;
    }
    
    public function setScmPath($value)
    {
        $this->_scmPath = $value;
        return $this;
    }
    
    public function getScmPath()
    {
        if (is_null($this->_scmPath)) {
            throw new Xulub_Migrate_Exception('Le chemin vers le binaire scm n\'est pas défini');
        }
        return $this->_scmPath;
    }
    
    abstract public function execute();
    
    public function enable()
    {
        $this->_isTest = false;
    }
    
    public function disable()
    {
        $this->_isTest = true;
    }
    
    public function isEnabled()
    {
        return !$this->_isTest;
    }
    
    protected function _write($command)
    {
        echo $command . PHP_EOL;
    }
    
    protected function _exec($command)
    {
        if ($this->_isTest === true)
        {
            $this->_write($command);
        }
        else
        {
            $this->_write($command);
            // exec     
            // si exec retourne 0, la commande s'est bien exécutée
            // si exec retourne 1 = problème
            /**
             * @todo : gérer les cas d'erreur
             */
            $retour = array();
            $retour = 0;
            exec($command, $output, $retour);
            if ($retour !== 0)
            {
                if (is_array($output)) {
                    foreach($output as $line) {
                        $this->_write($line . PHP_EOL);
                    }
                }
            }
        }
    }
}
