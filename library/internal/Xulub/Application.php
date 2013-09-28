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
 * @package    Xulub_Application
 * @see Zend_Application
 *
 * @desc Classe qui surcharge le Zend_Application par défaut
 * Conserve en cache (par défaut stockage sur le disque dans le répertoire
 * APPLICATION_PATH /share/cache) le fichier ou les fichiers de configuration
 * application.ini afin de ne pas le relire à chaque chargement de page
 *
 * largement inspiré de :
 * see @http://joegornick.com/2009/11/18/zend-framework-best-practices-part-1-getting-started/
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
require_once 'Zend/Application.php';

class Xulub_Application extends Zend_Application
{

    /**
     * Répertoire de cache par défaut
     * Chemin relatif par rapport à APPLICATION_PATH
     */
    const DEFAULT_CACHE_DIR = 'share/cache';

    /**
     * Flag used when determining if we should cache our configuration.
     */
    protected $_cacheConfig = true;

    /**
     *
     * @var Zend_Cache_Frontend
     */
    protected $_cache;

    /**
     * Liste des fichiers application.ini
     *
     * @var array
     */
    private $_configFiles = array();

    /**
     * Our default options which will use File caching
     * Le FrontEnd File caching permet de vérifier qu'un fichier n'a pas été
     *  modifié
     */
    protected $_cacheOptions = array(
        'frontendType' => 'File',
        'backendType' => 'File',
        'frontendOptions' => array(),
        'backendOptions' => array()
    );

    /**
     * Constructor
     *
     * Initialize application. Potentially initializes include_paths, PHP
     * settings, and bootstrap class.
     *
     * When $options is an array with a key of configFile, this will tell the
     * class to cache the configuration using the default options or c
     * acheOptions passed in.
     *
     * @param  string  $environment
     * @param  string|array|Zend_Config $options String path to configuration
     *  file, or array/Zend_Config of configuration options
     * @throws Zend_Application_Exception When invalid options are provided
     * @return void
     */
    public function __construct ($environment, $options = null)
    {
        if (is_array($options) && isset($options['config'])) {

            // First, let's check to see if there are any cache options
            if (isset($options['cacheOptions'])) {

                $this->_cacheOptions = array_merge(
                    $this->_cacheOptions,
                    $options['cacheOptions']
                );

                // on supprime cacheOptions car options non compatibles avec
                // Zend_Config
                unset($options['cacheOptions']);
            }
        }
        parent::__construct($environment, $options);
    }

    /**
     * Load configuration file of options.
     *
     * Optionally will cache the configuration.
     *
     * @param  string $file
     * @throws Zend_Application_Exception When invalid configuration file is
     * provided
     * @return array
     */
    protected function _loadConfig ($file)
    {
        if ($this->_cacheConfig === false) {
            return parent::_loadConfig($file);
        }

        $this->_configFiles[] = $file;

        $cache = $this->getCache();

        // Identifiant de cache associé au stockage du fichier de configuration
        $idCacheZendConfig = $this->getCacheId($file);

        // récupère la date de dernière modification du fichier
        $dateConfigFile = filemtime($file);

        /**
         * test() renvoie false si le cache n'existe pas ou
         * la date de dernière modification du cache
         */
        $testCache = $cache->test($idCacheZendConfig);

        /**
         * Si le cache n'existe pas ou qu'il est plus ancien que le fichier de
         *  configuration,
         * on recrée le fichier de cache
         */
        if ($testCache === false || $testCache <= $dateConfigFile) {
            $config = parent::_loadConfig($file);
            $cache->save($config, $idCacheZendConfig);
        } else {
            // Sinon, on lit le cache et on renvoie
            $config = $cache->load($idCacheZendConfig);
        }
        return $config;
    }

    /**
     * Renvoie l'identifiant de cache associé au fichier
     * Cette méthode est publique pour pouvoir récupérer l'identifiant de cache
     * pour les les tests unitaires
     *
     * @param string $file
     * @return string
     */
    public function getCacheId($file)
    {
        return 'Zend_Application_Config_' . md5($file);
    }

    /**
     * Renvoie un objet Zend_Cache qui va stocké le fichier application.ini
     *
     * @return Zend_Cache_Core|Zend_Cache_Frontend
     */
    public function getCache()
    {
        if (is_null($this->_cache)) {
            require_once 'Zend/Cache.php';
            $this->_cache = Zend_Cache::factory(
                $this->_cacheOptions['frontendType'],
                $this->_cacheOptions['backendType'],
                array_merge(
                    array(
                      // Frontend Default Options
                      'master_files' => $this->_configFiles,
                      'automatic_serialization' => true
                    ),
                    $this->_cacheOptions['frontendOptions']
                ),
                array_merge(
                    array(
                        // Backend Default Options
                        'cache_dir' => APPLICATION_PATH . '/' . self::DEFAULT_CACHE_DIR
                    ),
                    $this->_cacheOptions['backendOptions']
                )
            );
            return $this->_cache;
        } else {
            $this->_cache->setMasterFiles($this->_configFiles);
            return $this->_cache;
        }
    }

    /**
     * Passe en paramètre l'objet cache qui sera utilisé pour stocker
     * la configuration (application.ini)
     *
     * @param Zend_Cache_Frontend $cache
     * @return Xulub_Application
     */
    public function setCache(Zend_Cache_Frontend $cache)
    {
        $this->_cache = $cache;
        return $this;
    }
}