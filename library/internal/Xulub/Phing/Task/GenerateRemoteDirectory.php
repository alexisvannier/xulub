<?php
require_once "phing/Task.php";

/**
 * Tâche phing qui permet de se connecter à un serveur SSH et de
 * vérifier la possibilité de créer un répertoire
 *
 * Cettet tâche est principalement utilisée pour la livraision des sources
 * sur l'intranet
 *
 */
class GenerateRemoteDirectory extends Task
{
    private $_basedir;

    private $_returnProperty;

    private $_user;

    private $_host;

    function setBasedir ($basedir)
    {
        $this->_basedir = $basedir;
    }

    /**
     *
     * @param string $prop Nom de la propriété qui sera utilisée
     * pour retourner le répertoire à créer
     */
    function setReturnProperty ($prop)
    {
        $this->_returnProperty = $prop;
    }

    /**
     * User pour se connecter au serveur SSH
     *
     * @param string $user
     */
    function setUser ($user)
    {
        $this->_user = $user;
    }

    /**
     * Hôte du serveur SSH
     *
     * @param string $host
     */
    function setHost ($host)
    {
        $this->_host = $host;
    }

    function main ()
    {
        $letters = array('A', 'B', 'C', 'D', 'E', 'F');

        foreach ($letters as $letter) {

            $tempDir = $this->_basedir . '-' . $letter;
            $return = null;
            $command = "ssh $this->_user@$this->_host 'if [ -d $tempDir ];then exit 0;else exit 1; fi'";
            $this->log("passthru : " . $command);
            passthru($command, $return);
            if ($return == 1) {

                if ($this->_returnProperty) {

                    $this->project->setProperty(
                        $this->_returnProperty,
                        $tempDir
                    );
                }

                $this->log("Le repertoire " . $tempDir . " peut être creé");
                return;
            }
        }
    }
}