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
class Xulub_Application_Resource_xbUser extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Exemple un peu brutal d'initialisation d'un utilisateur
     * Nécessite de charger la session auparavant car les infos utilisateurs
     * sont stockés en session !
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('xbsession');
        $user = new Xulub_User();
        $userid = Xulub_Session::get('user_id');
        if (!empty($userid)) {
            $user->setUserId($userid);
            $user->setEstAuthentifier(true);
        }
        return $user;
    }
}