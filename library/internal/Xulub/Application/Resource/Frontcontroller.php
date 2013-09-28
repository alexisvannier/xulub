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

/**
 * FrontController sp�cifique au CNFPT qui utilise un dispatcher particulier
 * qui permettra notamment de ne pas d�finir le nom des contr�leurs avec des '-'
 * ou des points '.' pour s�parer les mots du contr�leur.
 *
 * Exemple :
 *  Dans le comportement par d�faut du Zend Framework, le contr�leur MaPage doit
 *  etre appel� par l'url http://domain.net/lang/profil/module/ma-page
 * Le m�canisme mis en place permettra d'appeler le controleur MaPageController
 * via l'URL http://domain.net/lang/profil/module/mapage ou
 * http://domain.net/lang/profil/module/MaPage
 *
 * On ne renomme par Xulub_Application_Resource_FrontController en
 * Xulub_Application_Resource_XbFrontController car les ressources
 * Zend_Application_Resource_Layout et Zend_Application_Resource_Modules sont
 * utilis�es "nativement" et elles utilisent l'appel des bootstrap
 * $this->getBootstrap()->bootstrap('FrontController');
 *
 * Il faudrait �galement modifier ces ressources pour pouvoir renommer la
 * ressource Xulub_Application_Resource_FrontController.
 *
 */
class Xulub_Application_Resource_FrontController
    extends Zend_Application_Resource_Frontcontroller
{
    public function init()
    {
        $frontController = Zend_Controller_Front::getInstance();

        // d�finir le r�pertoire de stockage des helpers
        // des controlleurs d'action
        /**
         * @todo : d�placer si possible le addPath dans la conf.
         */
        Zend_Controller_Action_HelperBroker::addPath(
            'Xulub/Controller/Action/Helper',
            'Xulub_Controller_Action_Helper'
        );

        // d�finition du dispatcher qui permet de d�sactiver
        // le - dans le nom des vues
        $dispatcher = new Xulub_Controller_Dispatcher_Standard();
        $frontController->setDispatcher($dispatcher);

        // on d�finit aucun s�parateur entre les mots du contr�leur
        $frontController->getDispatcher()->setWordDelimiter(array());
        return parent::init();
    }
}