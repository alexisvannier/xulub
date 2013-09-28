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
 * @desc On surcharge Zend_Application_Resource_Cachemanager simplement pour
 *  pouvoir stocker le cachemanger dans le registre
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Application_Resource_xbCacheManager extends Zend_Application_Resource_Cachemanager
{
    public function init()
    {
        $cachemanager = parent::init();
        $this->_enableLogger();
        Zend_Registry::set('cachemanager', $cachemanager);
        return $cachemanager;
    }

    /**
     * Active le log du cache uniquement si logging = true
     * exemple dans le application.ini :
     * resources.xbcachemanager.database.frontend.options.logging = true
     *
     * A priori, étant donné que nous utilisons le CacheManager, il n'est pas
     * possible de passer le logger dans la configuration (application.ini).
     * resources.xbcachemanager.database.frontend.options.logger = Zend_Log
     */
    private function _enableLogger()
    {
        $logger = null;

        foreach ($this->getCacheManager()->getCaches() as $cache) {

            if ((bool)$cache->getOption('logging') === true) {

                /**
                 * On initialise le logger qu'une seule fois
                 */
                if ($logger === null) {
                    $bootstrap = $this->getBootstrap();
                    $bootstrap->bootstrap('xbmultilog');
                    $logger = $bootstrap->getPluginResource('xbmultilog')->getLog();
                    $logger->debug('activation du logger pour le cache ');
                }

                /**
                 * On passe le logger aux instances de cache
                 */
                if ($logger instanceof Zend_Log) {
                    $cache->setOption('logger', $logger);
                }
            }
        }
    }
}