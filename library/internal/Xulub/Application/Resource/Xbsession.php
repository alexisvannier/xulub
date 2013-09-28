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
 * @category Xulub
 * @package Xulub_Application
 * @subpackage Xulub_Application_Resource
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Application_Resource_XbSession extends Zend_Application_Resource_Session
{
    /**
     * Méthode qui permet de construire le répertoire de stockage de session
     * en y ajoutant le nom de l'hôte courant
     *
     * @return string
     */
    public function getAddPathServer()
    {
        $path = $this->_options['save_path'];

        // On ajoute le serveur dans le répertoire de stockage
        // des sessions si nécessaire
        if (!empty($_SERVER['SERVER_NAME'])) {

            $path .= '/'.$_SERVER['SERVER_NAME'];

             if (!is_dir($path)) {
                    // On crée le répertoire en 0700
                    // pour éviter que tous les utilisateurs du système
                    // puisse lire le contenu des variables de session
                    // (faille de sécurité)
                    mkdir($path, 0700, true);
             }
        }
        return $path;
    }


    /**
     * Méthode d'initialisation de la ressource du Zend Application
     * Le code de parent::init a été repris ici afin de pouvoir gérer
     * les options pathaddserver et le démarrage automatique de la session
     *
     *  @return void
     */
    public function init()
    {
        $start = false;

        // Méthode reprise de Zend_Application_Resource_Session
        $options = array_change_key_case($this->getOptions(), CASE_LOWER);

        if (isset($options['savehandler'])) {
            unset($options['savehandler']);
        }

        /**
         * Si le paramètre save_path n'est pas défini, on met /tmp par défaut
         */
        if (empty($options['save_path'])) {
            $this->_options['save_path'] = '/tmp';
        }

        // ajoute l'hôte du serveur dans le save_path
        if (isset($options['pathaddserver'])) {

            if ((bool)$options['pathaddserver'] === true) {
                $path = $this->getAddPathServer();
                $options['save_path'] = $path;
            }

            // le paramètre est supprimé pour ne pas faire planter le contrôle
            // des options de Zend_Session
            unset($options['pathaddserver']);
        }

        // démarre la session si paramètre start passé dans le fichier de config
        if (isset($options['start'])) {

            if ((bool)$options['start'] === true) {
                $start = true;
            }

            // le paramètre est supprimé pour ne pas faire planter le contrôle
            // des options de Zend_Session
            unset($options['start']);
        }

        if (count($options) > 0) {
            Xulub_Session::setOptions($options);
        }

        if ($this->_hasSaveHandler()) {
            Xulub_Session::setSaveHandler($this->getSaveHandler());
        }

        // démarre la session si paramètre start passé dans le fichier de config
        if ($start === true) {
            Xulub_Session::start();
        }
    }
}