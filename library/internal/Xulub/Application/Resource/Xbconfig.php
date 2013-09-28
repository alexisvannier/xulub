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
class Xulub_Application_Resource_xbConfig extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        # ces constantes seront définies à partir de php 5.3
        # cf. http://php.net/manual/fr/errorfunc.constants.php
        if (!defined('E_USER_DEPRECATED')) {
            define('E_USER_DEPRECATED', E_USER_NOTICE);
        }

        if (!defined('E_DEPRECATED')) {
            define('E_DEPRECATED', E_USER_NOTICE);
        }

        $this->_initPluginLoaderCache();
    }

    /**
     * Méncanisme qui permet d'accélérer le chargement des plugins
     * cf. http://framework.zend.com/manual/fr/zend.loader.pluginloader.html#zend.loader.pluginloader.performance.example
     */
    private function _initPluginLoaderCache()
    {
        $options = $this->getOptions();

        if (isset($options['enablePluginLoaderCache'])
            && $options['enablePluginLoaderCache'] == true
        ) {
            $classFileIncCache = APPLICATION_PATH . '/share/cache/pluginLoaderCache.php';

            if (file_exists($classFileIncCache)) {
                include_once $classFileIncCache;
            }

            Zend_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);
        }
    }
}