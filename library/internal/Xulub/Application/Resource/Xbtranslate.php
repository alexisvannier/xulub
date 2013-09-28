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
class Xulub_Application_Resource_xbTranslate extends Zend_Application_Resource_Translate
{

    public function init()
    {
        $cache = null;
        $bootstrap = $this->getBootstrap();

        if ($bootstrap->hasPluginResource('xbcachemanager')) {

            $bootstrap->bootstrap('xbcachemanager');
            $cachemanager = $bootstrap->getResource('xbcachemanager');

            if ($cachemanager->hasCache('translate')) {
                $cache = $cachemanager->getCache('translate');
                Zend_Translate::setCache($cache);
            }
        }

        $options = $this->getOptions();

        // Si $options['force_compile] existe et est à true, on recompile
        // les fichiers GETTEXT
        if (isset($options['options']['force_compile'])) {

            if ((bool) $options['options']['force_compile'] === true) {

                $moCompiler = $options['options']['mo_compiler'];

                $xml2po = new Xulub_Utils_Xml2po(
                    APPLICATION_PATH . '/controllers',
                    APPLICATION_PATH . '/share/locale',
                    $moCompiler
                );
                
                $xml2po->convert();

                if (Zend_Translate::hasCache() === true) {
                    Zend_Translate::clearCache();
                }
            }
        }

        return parent::init();
    }
}