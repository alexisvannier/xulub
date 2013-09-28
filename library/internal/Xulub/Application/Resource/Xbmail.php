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
 * @desc Extends Zend_Application_Resource_Mail
 * Permet de charger la configuration du mail :
 *  - gestion des BCC par défaut
 *
 * @uses Zend_Application_Resource_Mail
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Application_Resource_Xbmail extends Zend_Application_Resource_Mail
{
    /**
     * Copie de Zend_Application_Resource_Mail::getMail
     * Remplacement de Zend_Mail par Xulub_Mail
     *
     * @return Zend_Mail_Transport_Abstract|null
     */
    public function getMail()
    {
        if (null === $this->_transport) {

            $options = $this->getOptions();

            foreach ($options as $key => $option) {
                $options[strtolower($key)] = $option;
            }

            $this->setOptions($options);

            if (isset($options['transport']) &&
               !is_numeric($options['transport'])
            ) {
                $this->_transport = $this->_setupTransport(
                    $options['transport']
                );

                if (!isset($options['transport']['register']) ||
                   $options['transport']['register'] == '1' ||
                   (isset($options['transport']['register']) &&
                        !is_numeric($options['transport']['register']) &&
                        (bool) $options['transport']['register'] == true)
                ) {
                    Xulub_Mail::setDefaultTransport($this->_transport);
                }
            }

            $this->_setDefaults('from');
            $this->_setDefaults('replyTo');
            $this->_setDefaults('bcc');
        }

        return $this->_transport;
    }

    /**
     * Copie de Zend_Resource_Application_Mail:_setDefaults
     * afin d'appeler Xulub_Mail et non plus, Zend_Mail
     *
     * On veut pouvoir gérer le setDefaults('bcc')
     *
     * @param string $type
     */
    protected function _setDefaults($type)
    {
        $key = strtolower('default' . $type);
        $options = $this->getOptions();

        if (isset($options[$key]['email']) &&
           !is_numeric($options[$key]['email'])
        ) {
            $method = array('Xulub_Mail', 'setDefault' . ucfirst($type));

            if (isset($options[$key]['name']) &&
               !is_numeric($options[$key]['name'])
            ) {
                call_user_func(
                    $method,
                    $options[$key]['email'],
                    $options[$key]['name']
                );
            } else {
                call_user_func($method, $options[$key]['email']);
            }
        }
    }
}