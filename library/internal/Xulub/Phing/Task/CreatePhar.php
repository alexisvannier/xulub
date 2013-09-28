<?php
require_once "phing/Task.php";

/*
 * Tâche phing permettant de créer une archive Phar.
 * On crée notre propre tâche Phing car la tâche par défaut est très lente.
 *
 * Usage :
 *      <owntaskphar
            destfile="${tempdir}/${package_name}.phar"
            sourcedir="${tempdir}/sources/${package_name}/"
         />
 *
 * Par défaut, cette tâche attend la présence d'un stub (fichier bootstrap de
 * phar) à la racine du projet.
 *
 * Les limites de ce mécanisme sont :
 *  * il n'est pas possible d'exclure certains répertoires
 *  * il n'est pas possible de configurer un stub particulier
 *
 *
 */

class CreatePhar extends Task
{
    /**
     * Chemin complet vers le fichier phar de destination
     *
     * @var string
     */
    public $destFile;

    /**
     * Répertoire source des sources
     */
    public $sourceDirectory;

    function setDestFile($destFile)
    {
        $this->destFile = $destFile;
    }

    function setSourceDir($sourceDirectory)
    {
        $this->sourceDir = $sourceDirectory;
    }

    function main()
    {
        if (is_file($this->destFile)) {
            unlink($this->destFile);
        }

        $phar = new Phar(
            $this->destFile,
            Phar::CURRENT_AS_FILEINFO | Phar::KEY_AS_FILENAME,
            $name
        );
        $phar->startBuffering();
        $phar->setStub(
            "<?php
include './stub.php'; __HALT_COMPILER(); ?>"
        );
        //$phar->buildFromDirectory($this->sourceDir, '/\.php$/');
        $phar->buildFromDirectory($this->sourceDir);
        # Ne fonctionne pas ?
        # $phar->compressFiles(Phar::GZ);
        $phar->stopBuffering();
    }
}
