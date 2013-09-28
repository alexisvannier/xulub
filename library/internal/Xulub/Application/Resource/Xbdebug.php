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
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

class Xulub_Application_Resource_XbDebug extends Zend_Application_Resource_ResourceAbstract
{

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Locale()
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $options = $this->getOptions();

        if (isset($options['enabled']) && $options['enabled'] == true) {

            $autoloader = $bootstrap->getApplication()->getAutoloader();
            $autoloader->registerNamespace('ZFDebug');

            // if plugins options is empty then set default
            if (!(isset($options['plugins'])
                && is_array($options['plugins']))
            ) {
                $options['plugins'] = array(
                    'Auth',
                    'Cache' => array(),
                    'Variables',
                    'Database',
                    //'Html',
                    // 'File' => array('base_path' => realpath(APPLICATION_PATH . '/../')),
                    'Memory',
                    'Time',
                    'Registry',
                    'Exception'
                );
            }

            $options['plugins'] = $this->_convertOptionsPlugins(
                $options['plugins']
            );

            // Setup the Database plugin
            if (isset($options['plugins']['Database'])) {
                // Instantiate the database adapter and setup the plugin.
                // Alternatively just add the plugin like above and rely on the
                // autodiscovery feature.
                if ($bootstrap->hasPluginResource('xbmultidb')) {
                    $bootstrap->bootstrap('xbmultidb');
                    $db = $bootstrap->getPluginResource('xbmultidb')->getDb();
                    $options['plugins']['Database']['adapter']['default'] = $db;
                }
            }

            // Setup the cache plugin
            if (isset($options['plugins']['Cache'])) {

                if ($bootstrap->hasPluginResource('xbcachemanager')) {

                    $bootstrap->bootstrap('xbcachemanager');
                    $caches = $bootstrap->getResource('xbcachemanager')->getCaches();

                    foreach ($caches as $name => $cache) {
                        $options['plugins']['Cache']['backend'][$name] = $cache->getBackend();
                    }
                }
            }

            $debug = new Xulub_Controller_Plugin_Debug($options);

            $bootstrap->bootstrapFrontController();
            $frontController = $bootstrap->getResource('frontController');
            $frontController->registerPlugin($debug);
        }
    }

    /**
     * On convertit le tableau en indiquant le nom de la clé pour le plugin
     * (au lieu d'une clé numérique)
     */
    private function _convertOptionsPlugins($plugins)
    {
        foreach ($plugins as $plugin => $options) {

            if (is_numeric($plugin)) {

                # Plugin passed as array value instead of key
                $pluginName = $options;
                $options = array();
                unset($plugins[$plugin]);
                $plugins[$pluginName] = $options;
            }
        }
        return $plugins;
    }

}