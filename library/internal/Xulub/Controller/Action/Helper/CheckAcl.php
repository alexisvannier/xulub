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
 * @package Xulub_Controller
 * @subpackage Xulub_Controller_Action
 *
 * @desc Helper de contr�leur d'action permettant de tester que l'utilisateur
 * dispose des droits n�cessaires.
 *
 * Le helper doit �tre charg� dans le bootstrap :
 *   $helperCheckacl = new Xulub_Controller_Action_Helper_CheckAcl();
 *   Zend_Controller_Action_HelperBroker::addHelper($helperCheckacl);
 *
 * Le Helper doit �tre initialis� auparavant (exemple dans un controller) :
 * if ($this->getUser() !== false)
 * {
 *    $this->_helper->CheckAcl->setUser($this->getUser());
 * }
 *
 * // permet de d�finit le format des Ressources
 * $this->_helper->CheckAcl->setRessourceFormat(
 *  Xulub_Controller_Action_Helper_CheckAcl::XULUB_ACL_FORMAT_BASEURL_MODULE_PAGE
 * );
 *
 * // Permet d'activer le helper
 * $this->_helper->CheckAcl->enable();
 *
 * // Permet de d�sactiver le helper
 * $this->_helper->CheckAcl->enable();
 *
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Controller_Action_Helper_CheckAcl extends Zend_Controller_Action_Helper_Abstract
{
    const XULUB_ACL_FORMAT_BASEURL_MODULE_PAGE = 'BaseUrl_Module_Page';
    const XULUB_ACL_FORMAT_MODULE_PAGE = 'Module_Page';
    const XULUB_ACL_FORMAT_PAGE = 'Page';

    /**
     *
     * @var Xulub_User
     */
    private $_user;

    /**
     * Format de la ressource � tester
     *
     * @see les constantes
     *
     * @var string
     */
    private $_ressourceFormat = self::XULUB_ACL_FORMAT_PAGE;

    /**
     *
     * @var Zend_Log
     */
    private $_log;

    /**
     * Activation des ACL
     *
     * @var bool
     */
    protected $_enabled = true;

    /**
     * Renvoie le nom de la ressource � tester selon le type de ressource
     *
     * @return string
     */
    private function _getRessourceName()
    {
        $ressourceName = null;
        $baseurl = str_replace('/', '', $this->getRequest()->getBaseUrl());
        $module = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();

        switch ($this->_ressourceFormat)
        {
            case self::XULUB_ACL_FORMAT_BASEURL_MODULE_PAGE :
                $ressourceName = $baseurl . '_' . $module . '_' . $controller;
                break;
            case self::XULUB_ACL_FORMAT_MODULE_PAGE :
                $ressourceName = $module . '_' . $controller;
                break;
            case self::XULUB_ACL_FORMAT_PAGE :
            default:
                $ressourceName = $controller;
                break;
        }
        return $ressourceName;
    }

    /**
     * V�rifie les autorisations de l'utilisateur selon la ressource courante
     *
     */
    public function preDispatch()
    {
        if ($this->_enabled === true) {

            if (Zend_Auth::getInstance()->hasIdentity() === true) {

                $identifiant = $this->_user->getLogin();

                try
                {
                    $ressource = $this->_getRessourceName();

                    // On teste les droits uniquement si la ressource est
                    // d�finie dans les ACL
                    if ($this->_user->acl->privileges->has($ressource)) {

                        if ($this->_user->isAllowed($ressource, 'consulter') === false) {

                            $message = 'Utilisateur (identifiant : ' . $identifiant . ') authentifi� mais ressource non accessible (' . $this->_getRessourceName() . ')';

                            $this->getActionController()->forwardForbidden(
                                $message
                            );
                        }
                    }
                }
                catch (Zend_Acl_Exception $e)
                {
                    throw new Xulub_Controller_Exception(
                        'Probl�me dans les ACL'
                    );
                }
            } else {
                $message = 'Utilisateur non identifi�';
                $this->getActionController()->forwardForbidden($message);
            }
        }
    }

    /**
     * D�finit l'utilisateur qui va �tre utilis� pour v�rifier les ACL
     *
     * @param Xulub $user
     * @return Xulub_Controller_Action_Helper_CheckAcl
     */
    public function setUser(Xulub_User $user)
    {
        $this->_user = $user;
        return $this;
    }

    /**
     * D�finit de ressource � Tester
     *
     * @param string $value
     * @return Xulub_Controller_Action_Helper_CheckAcl
     */
    public function setRessourceFormat($value)
    {
        $this->_ressourceFormat = (string) $value;
        return $this;
    }

    public function getRessourceFormat($value)
    {
        return $this->_ressourceFormat;
    }

    /**
     * D�sactivation du plugin de v�rification des ACL
     */
    public function disable()
    {
        $this->_enabled = false;
    }

    /**
     * Activation du plugin de v�rification des ACL
     */
    public function enable()
    {
        $this->_enabled = true;
    }

    /**
     * D�termine si le plugin de v�rification des ACL est actif
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }

    /**
     * D�finit un objet Log
     *
     * @param Zend_Log $log
     * @return Xulub_Controller_Action_Helper_CheckAcl
     */
    public function setLog(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }

    /**
     * Renvoie un objet Zend_log
     * @return Zend_Log
     */
    public function getLog()
    {
        return $this->_log;
    }
}
