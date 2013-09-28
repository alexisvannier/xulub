<?php

require_once "phing/Task.php";

/**
 * Tâche phing permettant de générer les fichiers xml2po
 *
 * Pour l'utiliser, il est nécessaire d'ajouter dans le fichier build.xml
 * <includepath classpath="${project.basedir}/../xulub-${framework_version}/library/internal" />
        <taskdef name="xml2po" classname="Xml2po" classpath="${project.basedir}/../xulub-${framework_version}/library/internal/Xulub/Phing/Task" />
        <xml2po
            compiler="/usr/bin/msgfmt"
            searchdir="${project.basedir}/application/controllers"
            localedir="${project.basedir}/application/share/locale" />
 *
 *
 * Suite à un bug dans phing 2.4.4, il n'est pas possible d'appeler la classe
 * Xulub_Phing_Task_Xml2po
 * see http://phing.info/trac/ticket/607
 */
class Xml2po extends Task
{
    /**
     * Chemin vers le binaire en charge de la compilation gettext
     *
     * @var string
     */
    private $_compiler = '/opt/csw/bin/gmsgfmt';

    /**
     * Répertoire à partir duquel les fichiers de traduction languages.xml
     * vont être cherchés
     *
     * @var string
     */
    private $_searchDir;

    /**
     * Répertoire de stockage des locales
     *
     * @var string
     */
    private $_localeDir;

    /**
     * Définit le chemin vers le compilateur gettext
     *
     * @param string $value
     */
    public function setCompiler($value)
    {
        $this->_compiler = (string) $value;
    }

    /**
     * Répertoire de stockage vers le répertoire de recherche des fichiers
     * de langue
     *
     * @param string $dir
     */
    public function setSearchDir($dir)
    {
        $this->_searchDir = $dir;
    }

    /**
     * Répertoire de stockage des locales
     *
     * @param string $dir
     */
    public function setLocaleDir($dir)
    {
        $this->_localeDir = $dir;
    }

    /**
     * Méthode qui va vérifier que les paramètres passées à la tâche sont
     *  correctes
     */
    public function _check()
    {
        if (!is_executable($this->_compiler)) {
            throw new BuildException(
                'Le compilateur gettext '
                . $this->_compiler . ' n\'est pas exécutable.'
            );
        }

        if (!is_dir($this->_searchDir) || !is_readable($this->_searchDir)) {
            throw new BuildException(
                'Le répertoire de recherche ('
                . $this->_searchDir . ') n\'existe pas ou n\'est pas lisible.'
            );
        }

        if (!is_dir($this->_localeDir) || !is_readable($this->_localeDir)) {
            throw new BuildException(
                'Le répertoire de stockage des locales ('
                . $this->_localeDir . ') n\'existe pas ou n\'est pas inscriptibles.'
            );
        }
    }

    public function main()
    {
        $this->_check();

        /**
         * On charge les classes qui vont être nécessaires à l'exécution de
         * cette tâche phing
         */
        require_once 'Xulub/Utils/Xml2po.php';
        require_once 'Xulub/Utils/File.php';

        $xml2po = new Xulub_Utils_Xml2po(
            $this->_searchDir,
            $this->_localeDir,
            $this->_compiler
        );

        $xml2po->convert();
        $this->log('compilation des fichiers de langues');
    }
}