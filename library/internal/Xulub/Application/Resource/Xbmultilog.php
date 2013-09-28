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

/**
 * Ressource pour charger le composant xbLog
 * Construction du logger. Possibilité d'avoir plusieurs rédacteurs.
 * Ceux-ci sont repris du fichier de configuration. Si rien on config, on crée
 * au moins le logger par défaut de l'init.
 */
class Xulub_Application_Resource_XbMultilog extends Zend_Application_Resource_ResourceAbstract
{

    /**
     * @var xbLog
     */
    protected $_log;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return xbLog()
     */
    public function init()
    {
        return $this->getLog();
    }

    /**
     * Attach logger
     *
     * @param  Zend_Log $log
     * @return Zend_Application_Resource_Log
     */
    public function setLog(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }


    public function getLog()
    {
        if (null === $this->_log) {

            $log = new Xulub_Log();
            $options = $this->getOptions();

            if (!empty($options)) {

                foreach ($options as $option) {

                    $log->init(
                        $option['writerName'],
                        (int)$option['filterParams']['priority']
                    );
                    $log->debug('Logger '.$option['writerName']);
                }
            } else {
                $log->init();
                $log->debug('Logger par défaut');
            }

            $this->_log = $log;
        }

        return $this->_log;
    }
}