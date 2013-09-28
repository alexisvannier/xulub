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
 * @package Xulub_Acl
 * @subpackage Xulub_Acl_Base
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Acl_Base
{
	/**
	 * Listes des r�les
	 *
	 * @var array
	 */
	private $_roles = array();

	/**
	 * Liste des ressources disponibles
	 *
	 * @var array
	 */
	private $_ressources = array();

	/**
	 * Moteur des r�gles ACL regroupant les privil�ges associ�s aux roles et
     * ressources
	 *
	 * @var Zend_Acl
	 */
	public $privileges;

	/**
	 * Initialise le moteur de r�gles
	 *
	 */
	public function __construct()
	{
		$this->privileges = new Zend_Acl();
	}

	/**
	 * Retourne la liste des r�les qui ont d�j� �t� charg�s
	 *
	 * @return array
	 */
	public function getRolesCharges()
	{
		return array_keys($this->_roles);
	}

	/**
	 * Retourne la liste des r�les qui ont d�j� �t� charg�s
	 *
	 * @return array
	 */
	public function getRessourcesCharges()
	{
		return array_keys($this->_ressources);
	}

	/**
	 * Ajoute une ressource � la liste des ressources
	 *
	 * @param string $ressource
	 * @param string $heritage
	 */
	public function addRessource($ressource, $heritage = null)
	{
	    try
	    {
		  if (key_exists($ressource, $this->_ressources)) {
			 // Test si les valeurs h�rit�es sont identiques pour cette
             // ressource
			 if ($this->_ressources[$ressource] === $heritage) {
			 	return true;
			 }

			 // Si la ressource est d�j� enregistr�e, on l'efface
			 if ($this->privileges->has($ressource)) {
			 	$this->privileges->remove($ressource);
			 }
		  }

		  // Ajoute la ressrouce dans la Registry
		  $this->privileges->add(new Zend_Acl_Resource($ressource), $heritage);
		  $this->_ressources[$ressource] = $heritage;
	    }
	    catch(Zend_Acl_Exception $e)
	    {
            throw new xbException(
                'XULUB_ACL_EXCEPTION',
                xbException::NIV_EXCEPTION_APPLICATIVE,
                'xbBaseAcl::addRessource - '.$e->getMessage()."\n"
            );
	    }
	}

	/**
	 * Ajoute un r�le � la liste des r�les
	 *
	 * @param string $role
	 * @param array $heritage
	 */
	public function addRole($role, $heritage = array())
	{
		if (key_exists($role, $this->_roles)) {
			// Test si les valeurs h�rit�es sont identiques pour ce r�le
			if ($this->_roles[$role] === $heritage) {
				return true;
			}

			// Si le r�le est d�j� enregistr�, on l'efface
			if ($this->privileges->hasRole($role)) {
				$this->privileges->removeRole($role);
			}
		}

		// Ajoute le r�le dans la Registry
		$this->privileges->addRole(new Zend_Acl_Role($role), $heritage);
		$this->_roles[$role] = $heritage;
	}

	/**
	 * Ajoute un privilege au moteur de r�gle. Retourne false si le r�le ou la
     * ressource n'existe pas.
	 *
	 * @param string $ressource
	 * @param boolean $allow
	 * @param string|null $role
	 * @param string|null $privilege
	 * @return boolean
	 */
	public function addPrivilege($ressource, $allow, $role, $privilege)
	{
		if (!is_null($role) && !$this->privileges->hasRole($role)) {
			//debug_display('XuluAcl : role incorrect :'.var_export($role, 1));
			return false;
		}

		if ($allow) {
			$this->privileges->allow($role, $ressource, $privilege);
		} else {
			$this->privileges->deny($role, $ressource, $privilege);
		}

		return true;
	}

	/**
	 * Test si l'acc�s � la ressource est autoris� pour ce r�le
	 *
	 * @param string $ressource
	 * @param string $privilege
	 * @param string $role
	 * @return boolean
	 */
	public function isAllowed($ressource, $privilege, $role)
	{
		if ($this->privileges->hasRole($role)
            && $this->privileges->has($ressource)
        ) {

			return $this->privileges->isAllowed($role, $ressource, $privilege);
		}
		return false;
	}
}