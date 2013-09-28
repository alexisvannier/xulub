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
class Xulub_Application_Resource_XbMultidb extends Zend_Application_Resource_Multidb
{
    /**
     * Flag indiquant si le cache manager a été chargé ou non
     *
     * @var bool
     */
    private $_cacheManagerLoaded = false;

    public function init()
    {
        /**
         * @var Zend_Application_Resource_Multidb
         */
        $multidb = parent::init();

        /**
         * On stocke dans le registry pour Xulub_Db
         *
         * @todo : Supprimer le multidb du registre
         */
        Zend_Registry::set('multidb', $multidb);
        $options = $this->getOptions();
//        foreach ($options as $id => $params) {
//            if(isset($params['options']['date_format']))
//            {
//                $this->_changeNlsDateFormat(
//                  $id,
//                  $params['options']['date_format']
//                );
//            }
//        }

        // On initialise les paramètres par défaut de Zend_Db_Table
        $this->setDefaultDbTableParams($multidb->getDb());

        return $multidb;
    }

    /**
     * Ajout une nouvelle connection au tableau des connections de base de
     * données
     *
     * @param string $id identifiant de la connexion
     * @param string $db
     */
    public function setDb($id, $db)
    {
        if (empty($id) && array_key_exists($id, $this->_db)) {
            throw new Zend_Application_Resource_Exception(
                '$id est vide ou exite déjà'
            );
        }

        if ($db instanceof Zend_Db_Adapter_Abstract) {
            $this->_dbs[$id] = $db;
        } else {
            throw new Zend_Application_Resource_Exception(
                '$db n\'est pas une instance de Zend_Db'
            );
        }
    }

    /**
     * Définit la connection à utiliser.
     *
     * @param string $name identifiant de la connexion
     */
    public function setCurrentConnection($name)
    {
        // on récupère la connection $name
        $db = Zend_Registry::get('multidb')->getDb($name);

        // on positionne cette connexion comme la connexion par défaut
        $this->_setDefault($db);

        $this->setDefaultDbTableParams($db);
    }

    /**
     * Retourne le nom de la connexion courante
     *
     * @return Zend_Db
     */
    public function getCurrentConnectionName()
    {
        foreach ($this->_dbs as $name => $db) {
            if ($db === $this->getDb()) {
                return $name;
            }
        }
        return 'unknown';
    }

    /**
     * Définit les paramètres par défaut de Zend_Db
     *  - Adapter par défaut pour Zend_Db_Table
     *  - Zend_Cache pour Zend_Db_Table
     *
     * @param Zend_Db_Adapter $db
     * @return Xulub_Application_Resource_XbMultidb
     */
    public function setDefaultDbTableParams($db)
    {
        // Définit l'adapter par défaut à utiliser sur Zend_Db_Table
        Zend_Db_Table_Abstract::setDefaultAdapter($db);

        if ($this->_cacheManagerLoaded === false
            && $this->getBootstrap()->hasPluginResource('xbcachemanager')
        ) {
            $this->getBootstrap()->bootstrap('xbcachemanager');
            $this->_cacheManagerLoaded = true;
        }

        if ($this->_cacheManagerLoaded === true) {
            $cachemanager = $this->getBootstrap()
                ->getPluginResource('xbcachemanager')
                ->getCacheManager();

            if ($cachemanager instanceof  Zend_Cache_Manager
                && $cachemanager->hasCache('database')
            ) {
                Zend_Db_Table_Abstract::setDefaultMetadataCache(
                    $cachemanager->getCache('database')
                );
            }
        }
        return $this;
    }

    /**
     * Retourne une chaîne de connexion DSN
     *
     * @todo A déplacer dans un Xulub_Db qui héritera de Zend_Db
     * @param string $connexion_name nom de la connexion
     * @return string
     */
    public function getDsn($name = null)
    {
        $dsn = '';

        if (is_null($name)) {
            $name = $this->getCurrentConnectionName();
        }

        if (!array_key_exists($name, $this->_dbs)) {
            $msg = 'La connexion ' . $name . ' que vous essayer d\'utiliser n\'existe pas.';
            throw new Zend_Application_Resource_Exception('XULUB_DB_DSN '.$msg);
        }

        $options = $this->getOptions();

        $dsn = $options[$name]['adapter'] . '://' . $options[$name]['username']
            . ':' . $options[$name]['password'] . '@' . $options[$name]['host']
            . '/' . $options[$name]['dbname'];

        return $dsn;
    }
}