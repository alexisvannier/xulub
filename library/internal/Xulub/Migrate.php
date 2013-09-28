<?php

/**
 * Ce script permet de faciliter la migration d'une ancienne version du framework
 * � une version utilisant les m�canismes du Zend Framework
 *
 * Pour tester la migration :
 * cd
 * svn co http://svn.dmncnfpt.local/svn/auth-intranet/branches/release-1.1.1-xulub-zf auth-intranet-xulub-zf --ignore-externals
 * php ~/xulub-zf/library/utils/migrate.php
 *
 * soit :
 * sudo rm -rf library/ application/ public/ && git reset --hard && php /home/dumef/dev-boulot/xulub-0.8.0/library/deprecated/utils/migrate.php
 * svn --recursive revert * && rm -rf library/ application/ public/ &&  php ../xulub-0.8.0/library/internal/Xulub/Migrate.php 
 *  2>/dev/null
 */
ini_set('memory_limit', '80M');

// On charge le Zend_Application et l'autoloader
$includePaths = array(
    realpath(dirname(__FILE__) . '/..'),
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $includePaths));

require_once "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Xulub_');


try
{
    $opts = new Zend_Console_Getopt(
        array(
            'help'           => 'Displays usage information.',
            'projet-dir=s'   => 'r�pertoire � migrer',
            'namespace=s'    => 'Namespace � utiliser',
            'scm=s'          => 'chemin complet vers le binaire svn ou git',
            'template-dir=s' => 'r�pertoire de stockage des templates pour ce projet',
            // r�pertoire � exclure des traitements (exemple : ressources externes)
            // ces r�pertoires sont d�plac�s � la mano dans un r�pertoire exclude
            'exclude=s'      => 'r�pertoire � exclure des traitements s�par�s par des virgules',
            'dry-run'        => 'permet de tester la migration sans l\'�x�cuter'
        )
    );
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    exit($e->getMessage() . "\n\n" . $e->getUsageMessage());
}

/**
 * Si flag = help ou pas de param�tres, on affiche l'aide
 */
if (count($opts->getOptions()) == 0 || isset($opts->help))
{
    echo $opts->getUsageMessage();
    exit;
}

/**
 * R�cup�rer le r�pertoire du projet � migrer
 */
if (isset($opts->{'projet-dir'}))
{
    $projectDir = $opts->{'projet-dir'};
    if (!is_dir($projectDir))
    {
        exit('Le r�pertoire ' . $projectDir . ' n\'existe pas.' . PHP_EOL);
    }
} else {
    echo $opts->getUsageMessage();
    exit;
}

/**
 * R�cup�rer le r�pertoire de stockage des mod�les � utiliser pour la migration
 */
if (isset($opts->{'template-dir'}))
{
    $templateDir = $opts->{'template-dir'};
    if (!is_dir($templateDir))
    {
        exit('Le r�pertoire ' . $templateDir . ' n\'existe pas.' . PHP_EOL);
    }
} else {
    echo $opts->getUsageMessage();
    exit;
}

/**
 * R�cup�re le namespace
 */
$projectNamespace = '';
if (isset($opts->namespace))
{
    $projectNamespace = $opts->getOption('namespace');
} else {
    echo $opts->getUsageMessage();
    exit;
}

/**
 * R�cup�re les r�pertoires � exclure de la migration
 */
if (isset($opts->{'exclude'}))
{
    $tmp = $opts->getOption('exclude');
    $excludeDir = explode(',', $tmp);
    foreach($excludeDir as $dir)
    {
        if(!is_dir($dir))
        {
            exit('Le r�pertoire ' . $dir . ' de exclude-dir n\'existe pas.' . PHP_EOL);
        }
    }
}

/**
 * R�cup�re le chemin vers le binaire scm � utiliser
 */
if (isset($opts->scm))
{
    $scmPath = $opts->getOption('scm');
    if (!is_executable($scmPath))
    {
        exit($scmPath . ' n\'existe pas ou n\'est pas ex�cutable.' . PHP_EOL);
    }
} else {
    echo $opts->getUsageMessage();
    exit;
}

$dryrun = false;
if (isset($opts->{'dry-run'}))
{
    $dryrun = true;
}


$migration = new Xulub_Migrate_MigrationV7V8(
    $projectDir,
    $projectNamespace,
    $templateDir,
    $excludeDir,
    $scmPath
);

$migration->log('');
$migration->log('R�capitulatif : ');
$migration->log('--------------- ');
$migration->log('r�pertoire � migrer      : ' . $projectDir);
$migration->log('r�pertoire des templates : ' . $templateDir);
$migration->log('namespace                : ' . $projectNamespace);
$migration->log('scm                      : ' . $scmPath);
$migration->log(' ');

if ($dryrun === true) {
    $migration->enableTest();
} else {
    $migration->disableTest();
}

$migration->log('D�but du traitement : ');
$migration->log('--------------------- ');
$migration->log('');

$migration->start();

$migration->log('');
$migration->log('FIN du traitement : ');
$migration->log('--------------------- ');
$migration->log('');

$migration->finalize();

