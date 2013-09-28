<?php

/**
 * D�finition de la classe de migration
 */
abstract class Xulub_Migrate_MigrationAbstract
{
    /**
     * Si vrai, l'objet ne fait qu'afficher les commandes � ex�cuter
     * @var boolean
     */
    private $_isTest = true;
        
    /**
     * Tableau de concordance entre les anciens et les nouveaux noms des classes
     * Cl�    : ancienne classe
     * Valeur : nouvelle classe
     * @var array
     */
    protected $_classToReplace = array();
    
    /**
     * R�pertoire racine du projet
     * @var string
     */
    protected $_projetDir;
    
    /**
     * Contient les r�pertoires qu'il est n�cessaire d'exclure de la migration
     * (exemple : ressources externes, ...)
     * 
     * @var array
     */
    protected $_excludeDir;
    
    /**
     * Espace de nom du projet
     * @var string
     */
    protected $_projectNamespace;
    
    /**
     * r�pertoire cible qui contiendra les controllers
     * @var string
     */
    protected $_controllerDir;
    
    
    /**
     * r�pertoire qui contiendra les model et autres objets
     * @var string
     */
    protected $_libraryNamespaceDir;
    
    
    /**
     * r�pertoire qui contiendra les objets m�tiers
     * @var string
     */
    protected $_modelDir;
    
    /**
     * R�pertoire o� sont stock�s les templates pour la migration de l'application
     * 
     * @var string
     */
    protected $_templateDir;
    
    /**
     * Tableau contenant l'ensemble des commandes (Xulub_Migrate_Command_Abstract) � �x�cuter
     * @var array
     */
    protected $_commands;
    
    protected $_scmPath;

    public function __construct(
        $projetDir, 
        $projectNamespace, 
        $templateDir, 
        $excludeDir = array(),
        $scmPath = '/opt/csw/bin/svn'
    )
    {
        $this->_projetDir = $projetDir;
        $this->_projectNamespace = $projectNamespace;
        $this->_excludeDir = $excludeDir;

        $this->_controllerDir = $this->_projetDir . '/application/controllers';

        $this->_configDir = $this->_projetDir . '/application/configs';

        $this->_libraryNamespaceDir = $this->_projetDir . '/library/' . $projectNamespace;
        $this->_modelDir = $this->_libraryNamespaceDir . '/Model/';
        
        $this->_templateDir = $templateDir . DIRECTORY_SEPARATOR . strtolower($this->_projectNamespace);
        $this->_scmPath = $scmPath;
        
        // changement du r�pertoire par d�faut
        chdir($this->_projetDir);
    }

    public function __destruct()
    {
        
    }

    public function enableTest()
    {
        $this->_isTest = true;
    }

    public function disableTest()
    {
        $this->_isTest = false;
    }

    public function isTest()
    {
        return $this->_isTest;
    }
    
    public function setTemplateDir($value)
    {
        $this->_templateDir = $value;
        return $this;
    }
    
    public function getTemplateDir()
    {
        return $this->_templateDir;
    }
    
    public function setScmPath($value)
    {
        $this->_scmPath = $value;
        return $this;
    }
    
    public function getScmPath()
    {
        return $this->_scmPath;
    }

    /**
     * D�placement d'un r�pertoire
     *
     * @param string $origine
     * @param string $destination
     */
    public function move($origine, $destination)
    {
        foreach($this->_excludeDir as $dir) {
            if (strpos($origine, $dir) !== FALSE) {
                $this->log(
                    '  /!\ Vous essayez de d�placer un r�pertoire exclu (' . $origine . '). '
                    . 'Il ne sera pas d�plac�.'
                );
                return;
            }
        }

        $this->addCommand(
            new Xulub_Migrate_Command_Move(
                $origine,
                $destination
            )    
        );
    }

    /**
     * Ajout d'un fichier avec son contenu
     * 
     * @param string $file
     * @param string $content 
     */
    public function add($file, $content)
    {
        if (in_array($origine, $this->_excludeDir)) {
            $this->log(
                '  /!\ Vous essayez de d�placer un r�pertoire exclu : ' . $origine
                . ' - Il ne sera pas d�plac�.'
            );
        }
        else {
            $this->addCommand(
                new Xulub_Migrate_Command_Add(
                    $file,
                    $content
                )
            );
        }
    }
    
    public function addCommand(Xulub_Migrate_Command_Abstract $command)
    {
        $this->_commands[] = $command;
    }

    /**
     * Ajout d'un fichier avec son contenu
     * 
     * @param string $file
     * @param string $content 
     */
    public function delete($path)
    {
        $this->addCommand(
            new Xulub_Migrate_Command_Delete(
                $path
            )
        );
    }

    /**
     * Ajout d'un r�pertoire
     * 
     * @param string $directory 
     */
    public function addDirectory($directory)
    {
        // On supprime le chemin complet du directory
        // pour �viter l'erreur git suivante : 
        // fatal: '/home/dumef/gpa/application' is outside repository
        // 
        // Exemple : 
        //      /home/dumef/gpa/application 
        //   devient
        //      application
        //$directory = substr($directory, strlen($this->_projetDir)+1);
        $this->addCommand(
            new Xulub_Migrate_Command_AddDirectory(
                $directory
            )
        );
    }

    abstract public function start();

    public function log($msg)
    {
        $this->_commands[] = new Xulub_Migrate_Command_Log(
                $msg
        );
    }

    public function excludeDir()
    {
        $this->log('');
        $this->log(' # exclude directory ');
        $this->log(' # ----------------- ');
        $this->log('');

        if (count($this->_excludeDir) > 0) {
            foreach ($this->_excludeDir as $dir) {
                $this->delete($dir);
            }
        }
        else {
            $this->log('  * aucun r�pertoire � exclure.');
        }
        $this->log('');
    }

    public function finalize()
    {
        foreach ($this->_commands as $command) {
            if ($command instanceof Xulub_Migrate_Command_Abstract) {
                // Si on n'est pas en test, on ex�cuter les commandes
                if ($this->isTest() === false) {
                    $command->enable();
                }
                $command->setScmPath($this->getScmPath());
                
                $command->execute();
            }
        }
    }

    /**
     * Renvoie un r�cursif DirectoryIterator sur le r�pertoire $path
     * Possibilit� de ne recup�rer que certains fichiers via $pattern (*.php)
     * 
     * @param string $path
     * @param string $pattern
     * @return RecursiveIteratorIterator 
     */
    protected function _getIterator($path, $pattern = '')
    {
        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);
        if (empty($pattern)) {
            return $iterator;
        }
        else {
            // $pattern = '/^.+\.php$/i'; 
            $regex = new RegexIterator(
                    $iterator,
                    $pattern,
                    RecursiveRegexIterator::GET_MATCH
            );
            return $regex;
        }
    }

}
