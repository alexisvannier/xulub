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
 * @uses       Xulub_Controller_Action_Helper_Abstract
 * @category   Xulub
 * @package    Xulub_Controller
 * @subpackage Xulub_Controller_Action
 *
 * @desc Helper for creating Forms
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */

/**
 * @see Zend_Controller_Action_Helper_Abstract
 */
Zend_Loader::loadClass("HTML_QuickForm_Renderer_ArraySmarty");

class Xulub_Controller_Action_Helper_XbForm extends Zend_Controller_Action_Helper_Abstract
{
     /**
     * Ajoute un nouveau formulaire QuikForm à la vue
     *
     * @param string $name
     * @param string $method
     * @param string $action
     * @return HTML_QuickForm
     */
    public function addForm($name, $method='post', $action = '')
    {
        //Zend_Registry::get('log')->debug('creation formulaire ' . $name);
        if (empty($action)) {
            $url = Zend_Controller_Action_HelperBroker::getStaticHelper('Url');
            $action = $url->Url();
        }

        $this->_actionController->view->forms[$name] = new Xulub_Form(
            $name,
            $method,
            $action
        );

        return $this->_actionController->view->forms[$name];
    }

    /**
    * Retourne le formulaire $name associée à la vue
    *
    * @param string $name
    * @return HTML_QuickForm|false
    */
    public function getForm($name)
    {
        if (isset($this->_actionController->view->forms[$name])) {
            return $this->_actionController->view->forms[$name];
        }
        return false;
    }

    /**
    * Fonction qui désactive un formulaire quickform
    *
    * @param string $name : nom du formulaire
    * @return boolean
    */
    public function unsetForm($name)
    {
        if (isset($this->_actionController->view->forms[$name])) {
            unset($this->_actionController->view->forms[$name]);
            return true;
        }
        return false;
    }
}