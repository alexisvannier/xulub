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
 * @uses       Zend_Controller_Action
 * @category   Xulub
 * @package    Xulub_Controller
 * @subpackage Xulub_Controller_Action
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Controller_Action extends Zend_Controller_Action
{
    /**
     *
     * @param string $key
     * @param Object $value
     * @return View
     */
    public function assign($key, $value = null)
    {
         return $this->view->assign($key, $value);
    }

    /**
     * Renvoie l'objet Log s'il existe false sinon
     *
     * Appel le bootstrap. C'est mieux que de passer par le Registry
     *
     * @return Zend_Log|false
     */
    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('xbmultilog')) {
            return false;
        }
        $log = $bootstrap->getResource('xbmultilog');
        return $log;
    }

    /**
     * Renvoie l'objet Xulub_User
     *
     * @return Xulub_User
     */
    public function getUser()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('xbuser')) {
            return false;
        }
        $user = $bootstrap->getResource('xbuser');
        return $user;
    }
}