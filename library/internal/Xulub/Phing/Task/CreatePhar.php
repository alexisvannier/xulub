<?php
require_once "phing/Task.php";

/*
 * T�che phing permettant de cr�er une archive Phar.
 * On cr�e notre propre t�che Phing car la t�che par d�faut est tr�s lente.
 *
 * Usage :
 *      <owntaskphar
            destfile="${tempdir}/${package_name}.phar"
            sourcedir="${tempdir}/sources/${package_name}/"
         />
 *
 * Par d�faut, cette t�che attend la pr�sence d'un stub (fichier bootstrap de
 * phar) � la racine du projet.
 *
 * Les limites de ce m�canisme sont :
 *  * il n'est pas possible d'exclure certains r�pertoires
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
     * R�pertoire source des sources
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
