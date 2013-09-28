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
 * @category   Xulub
 * @package    Xulub_Bootstrap
 * @see Zend_Application_Bootstrap_Bootstrap
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * @todo : Voir si on ne peux pas déplacer ce code dans xbconfig
     */
    protected function _initAutoload()
    {
        $resources = new Zend_Loader_Autoloader_Resource(
            array(
                'namespace' => '',
                'basePath' => APPLICATION_PATH
            )
        );

        $resources->addResourceType('model', 'models', 'Model');

//        $loader = new Zend_Application_Module_Autoloader(array(
//         'namespace' => 'Navigation',
//         'basePath'  => APPLICATION_PATH . '/controllers/Navigation',
//        ));
    }

    /**
     * @todo : vérifier que c'est toujours utile.
     */
    protected function _initConfiguration()
    {
        $config = new Zend_Config($this->getApplication()->getOptions());
        Zend_Registry::set('appli-config', $config);
    }
}