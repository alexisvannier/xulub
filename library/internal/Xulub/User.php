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
 * @package Xulub_User
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_User
{
    /**
     * ID de l'utilisateur
     *
     * @var string
     */
    private $_userid;
    /**
     * Login de l'utilisateur
     *
     * @var sring
     */
    private $_login;

    /**
     * Détermine si la fonction d'initilisation de l'utilisateur
     * a été exécutée
     *
     * @var boolean
     */
    private $_isInitialized = false;

    /**
     * Indique si l'utilisateur est authentifier
     *
     * @var booleen
     */
    private $_authentifier = false;

    /**
     * moteur de règles ACL chargé selon le profil
     *
     * public : pour les ajouts de rôle à la volée
     *
     * @var xbBaseAcl
     */
    public $acl;

    /**
     * Retourne si l'objet a déjà été insctancié.
     *
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->_isInitialized;
    }

    /**
     * Assigne l'identifiant utilisateur
     *
     * @param integer $userid
     */
    public function setUserId($userid)
    {
        $this->_userid = $userid;
    }

    /**
     * Retourne l'identifiant utilisateur
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->_userid;
    }

    /**
     * Assigne le login utilisateur
     *
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->_login = $login;
    }

    /**
     * Retourne le login utilisateur
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * @deprecated remplacée par getEstAuthentifier()
     * Fonction synonymes à getEstAuthentifier()
     *
     * @return boolean
     */
    public function estAuthentifier()
    {
        return $this->getEstAuthentifier();
    }

    /**
     * Change l'indicateur d'authentification
     *
     * @param boolean $value
     */
    public function setEstAuthentifier($value = false)
    {
        $this->_authentifier = $value;
    }

    /**
     * Retourne l'indicateur d'authentification
     *
     * @return boolean
     */
    public function getEstAuthentifier()
    {
        return $this->_authentifier;
    }

    /**
     * déconnecte l'utilisateur :
     *  * détruit la session
     *  * ...
     *
     * @return boolean
     */
    public static function logout()
    {
        // Réintialise la session si le navigateur n'acceptait pas les cookies
        // faut-il le faire tout le temps ?
        if (!Xulub_Session::acceptCookie()) {
            Xulub_Session::regenerateId();
        }

        // Si une session utilisateur existe, on supprime la session
        if (Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Auth::getInstance()->clearIdentity();
            //Zend_Registry::get('log')->info(
            //"Authentification Logout Success: login=" . Zend_Auth::getInstance()->getIdentity()
            //);
            return true;
        }
        return false;
    }

    /**
     * Charge les ACL en fonction du contexte de l'utilisateur
     *
     */
    public function loadAcl($roleMaxlevel = 1, $rolesLocaux = array())
    {
        // par défaut, aucune ACL
        //  on définit pour chaque utilisateur ses ACL
        $classname = xbRegistry::get('modules-config')->getUserClassAcl();

        //$this->acl = XulubAcl::load($role_maxlevel, $roles_locaux);

        $this->acl = call_user_func(
            array($classname, 'load'),
            $roleMaxlevel,
            $rolesLocaux
        );
    }

    /**
     * Interroge le moteur ACL
     *
     * @param string $ressource
     * @param string $privilege
     * @param string $role
     * @return boolean
     */
    public function isAllowed($ressource, $privilege, $role = 'user')
    {

        $acceptation = false;
        if (isset($this->acl)) {
            $acceptation = $this->acl->isAllowed($ressource, $privilege, $role);
            //Zend_Registry::get('log')->warn(
            //'xbUser::isAllowed : acces ' . $role . '/' . $ressource . '/' . $privilege . ' : ' . var_export($acceptation, true)
            //);
        } else {
            //Zend_Registry::get('log')->warn(
            //'xbUser::isAllowed : PAS D\'ACL!!! checking ' . $role . '/' . $ressource . '/' . $privilege
            //);
        }
        return $acceptation;
    }

    /**
     * Retourne true si le rôle est chargé.
     *
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return $this->acl->privileges->hasRole($role);
    }

}