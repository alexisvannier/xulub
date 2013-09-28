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
     * M�thode qui permet de construire le r�pertoire de stockage de session
     * en y ajoutant le nom de l'h�te courant
     *
     * @return string
     */
    public function getAddPathServer()
    {
        $path = $this->_options['save_path'];

        // On ajoute le serveur dans le r�pertoire de stockage
        // des sessions si n�cessaire
        if (!empty($_SERVER['SERVER_NAME'])) {

            $path .= '/'.$_SERVER['SERVER_NAME'];

             if (!is_dir($path)) {
                    // On cr�e le r�pertoire en 0700
                    // pour �viter que tous les utilisateurs du syst�me
                    // puisse lire le contenu des variables de session
                    // (faille de s�curit�)
                    mkdir($path, 0700, true);
             }
        }
        return $path;
    }


    /**
     * M�thode d'initialisation de la ressource du Zend Application
     * Le code de parent::init a �t� repris ici afin de pouvoir g�rer
     * les options pathaddserver et le d�marrage automatique de la session
     *
     *  @return void
     */
    public function init()
    {
        $start = false;

        // M�thode reprise de Zend_Application_Resource_Session
        $options = array_change_key_case($this->getOptions(), CASE_LOWER);

        if (isset($options['savehandler'])) {
            unset($options['savehandler']);
        }

        /**
         * Si le param�tre save_path n'est pas d�fini, on met /tmp par d�faut
         */
        if (empty($options['save_path'])) {
            $this->_options['save_path'] = '/tmp';
        }

        // ajoute l'h�te du serveur dans le save_path
        if (isset($options['pathaddserver'])) {

            if ((bool)$options['pathaddserver'] === true) {
                $path = $this->getAddPathServer();
                $options['save_path'] = $path;
            }

            // le param�tre est supprim� pour ne pas faire planter le contr�le
            // des options de Zend_Session
            unset($options['pathaddserver']);
        }

        // d�marre la session si param�tre start pass� dans le fichier de config
        if (isset($options['start'])) {

            if ((bool)$options['start'] === true) {
                $start = true;
            }

            // le param�tre est supprim� pour ne pas faire planter le contr�le
            // des options de Zend_Session
            unset($options['start']);
        }

        if (count($options) > 0) {
            Xulub_Session::setOptions($options);
        }

        if ($this->_hasSaveHandler()) {
            Xulub_Session::setSaveHandler($this->getSaveHandler());
        }

        // d�marre la session si param�tre start pass� dans le fichier de config
        if ($start === true) {
            Xulub_Session::start();
        }
    }
}