<?php
/**
 * This file is part of XULUB.
 *
 * XULUB is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.

 * XULUB is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with XULUB; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */

// definit la version du framework à utiliser
// @todo : remplacer tout ça, par une fonction ou une méthode statique
defined('FRAMEWORK_VERSION')
        || define('FRAMEWORK_VERSION', '0.8.0');

// définit le répertoire application (de ZF)
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// définit le contexte de l'application (developpement / recette /production)
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'stage'));

// définit le répertoire où se trouve le framework
defined('FRAMEWORK_PATH')
        || define('FRAMEWORK_PATH', realpath(APPLICATION_PATH . '/../../xulub-' . FRAMEWORK_VERSION));

// définit le include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(FRAMEWORK_PATH . '/library/internal'), # répertoire où se trouve ZF
            '.'                                             # répertoire courant
        )));

// on charge le Xulub_Application (qui va ensuite s'occupe de charger les plugins)
// Xulub_Application est un héritage de Zend_Application permettant de gérer le cache
// sur le fichier de configuration
require_once 'Xulub/Application.php';
try
{
    $application = new Xulub_Application(
                    APPLICATION_ENV,
                    array(
                        'configFile' => APPLICATION_PATH . '/configs/application.ini'
                    )
    );
    $application->bootstrap()
            ->run();
}
catch (Zend_Exception $e)
{
    /**
     * En cas d'erreur, on affiche la page de maintenance.
     * L'erreur d'exécution est renvoyé au gestionnaire global de gestion des erreurs
     * (fichiers de log de PHP)
     */
    //require(dirname(__FILE__) . '/maintenance.html');
    // on fait un trigger pour logger dans le log de PHP (définit par l'environnement)
    trigger_error('Problème dans le chargement de l\'application : ' . $e->getMessage());
    // pour débugger :
    if (APPLICATION_ENV !== 'production') {
       var_dump($e);
    }
}
