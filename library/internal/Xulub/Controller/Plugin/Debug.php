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
 * @subpackage Xulub_Controller_Plugin
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Controller_Plugin_Debug extends ZFDebug_Controller_Plugin_Debug
{
    protected function _loadPlugins()
    {
        // liste des plugins dont le nom doit être converti
        $convert = array(
            'Auth'      => 'ZFDebug_Controller_Plugin_Debug_Plugin_Auth',
            'Database'  => 'Xulub_Controller_Plugin_Debug_Plugin_Database',
            'Variables' => 'Xulub_Controller_Plugin_Debug_Plugin_Variables',
        );

        foreach ($convert as $source => $destination) {
            $this->_convertPluginName($source, $destination);
        }

        parent::_loadPlugins();
    }

    /**
     * Méthode permettant de convertir le nom d'un plugin en sa classe
     * Cette méthode permet simplement de continuer à utiliser le nom réduit
     * pour en fait appeler les plugins de Xulub
     *
     * Avec cette méthode, l'utilisation du nom de plugins Variables permettra
     * d'appeler Xulub_Controller_Plugin_Debug_Plugin_Variables (et non pas,
     * Zend_Controller_Plugin_Debug_Plugin_Variables)
     *
     * @param string $source
     * @param string $destination
     */
    protected function _convertPluginName($source, $destination)
    {
        if (isset($this->_options['plugins'][$source])) {
            $config = $this->_options['plugins'][$source];
            unset($this->_options['plugins'][$source]);
            $this->_options['plugins'][$destination] = $config;
        }
    }

    /**
     * Appends Debug Bar html output to the original page
     * On surcharge car bug dans la version  actuelle
     * see http://code.google.com/p/zfdebug/issues/detail?id=49#c1
     *
     * @param string $html
     * @return void
     */
    protected function _output($html)
    {
        $response = $this->getResponse();
        $response->setBody(
            str_ireplace(
                '</body>',
                '<div id="ZFDebug_debug">' . $html . '</div>'
                . $this->_headerOutput() . '</body>', $response->getBody()
            )
        );
    }

    /**
     * Déterminer si le plugin est chargé dans le débugger
     *
     * @param string $identifier
     * @return bool
     */
    public function hasPlugin($identifier)
    {
        $identifier = strtolower($identifier);
        return isset($this->_plugins[$identifier]);
    }
}
