<?php

/**
 * Définition de la classe de migration
 */
abstract class Xulub_Migrate_MigrationAbstract
{
    /**
     * Si vrai, l'objet ne fait qu'afficher les commandes à exécuter
     * @var boolean
     */
    private $_isTest = true;
        
    /**
     * Tableau de concordance entre les anciens et les nouveaux noms des classes
     * Clé    : ancienne classe
     * Valeur : nouvelle classe
     * @var array
     */
    protected $_classToReplace = array();
    
    /**
     * Répertoire racine du projet
     * @var string
     */
    protected $_projetDir;
    
    /**
     * Contient les répertoires qu'il est nécessaire d'exclure de la migration
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
     * répertoire cible qui contiendra les controllers
     * @var string
     */
    protected $_controllerDir;
    
    
    /**
     * répertoire qui contiendra les model et autres objets
     * @var string
     */
    protected $_libraryNamespaceDir;
    
    
    /**
     * répertoire qui contiendra les objets métiers
     * @var string
     */
    protected $_modelDir;
    
    /**
     * Répertoire où sont stockés les templates pour la migration de l'application
     * 
     * @var string
     */
    protected $_templateDir;
    
    /**
     * Tableau contenant l'ensemble des commandes (Xulub_Migrate_Command_Abstract) à éxécuter
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
        
        // changement du répertoire par défaut
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
     * Déplacement d'un répertoire
     *
     * @param string $origine
     * @param string $destination
     */
    public function move($origine, $destination)
    {
        foreach($this->_excludeDir as $dir) {
            if (strpos($origine, $dir) !== FALSE) {
                $this->log(
                    '  /!\ Vous essayez de déplacer un répertoire exclu (' . $origine . '). '
                    . 'Il ne sera pas déplacé.'
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
                '  /!\ Vous essayez de déplacer un répertoire exclu : ' . $origine
                . ' - Il ne sera pas déplacé.'
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
     * Ajout d'un répertoire
     * 
     * @param string $directory 
     */
    public function addDirectory($directory)
    {
        // On supprime le chemin complet du directory
        // pour éviter l'erreur git suivante : 
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
            $this->log('  * aucun répertoire à exclure.');
        }
        $this->log('');
    }

    public function finalize()
    {
        foreach ($this->_commands as $command) {
            if ($command instanceof Xulub_Migrate_Command_Abstract) {
                // Si on n'est pas en test, on exécuter les commandes
                if ($this->isTest() === false) {
                    $command->enable();
                }
                $command->setScmPath($this->getScmPath());
                
                $command->execute();
            }
        }
    }

    /**
     * Renvoie un récursif DirectoryIterator sur le répertoire $path
     * Possibilité de ne recupérer que certains fichiers via $pattern (*.php)
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
