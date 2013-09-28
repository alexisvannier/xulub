<?php

/**
 * This class does contain all necessary methods for the HTML cache with
 * ZendCache
 *
 * Implements ZendCacheManager resource for the HTML cache
 *
 * Cette classe hérite de Smarty_CacheResource_KeyValueStore qui est la classe
 * fournie par Smarty à partir de la version 3.1.1 qui correspond à un stockage
 * clé/valeur
 *
 * @package Xulub
 * @subpackage Xulub_Smarty
 */

class Smarty_CacheResource_ZendCache extends Smarty_CacheResource_KeyValueStore
{
    /**
     *
     * @var Zend_Cache_Core
     */
    private $_cache = null;

    public function __construct()
    {
        if (!Zend_Registry::isRegistered('cachemanager')) {
            throw new SmartyException('Pas de cachemanager dans Zend_Registry');
        }

        if (!Zend_Registry::get('cachemanager')->hasCache('template')) {

            throw new SmartyException(
                'CacheManager "template" non initialisé.'
            );
        } else {
            $this->_cache = Zend_Registry::get('cachemanager')->getCache('template');
        }
    }

    /**
     * Read values for a set of keys from cache
     *
     * @param array $keys list of keys to fetch
     * @return array list of values with the given keys used as indexes
     */
    protected function read(array $keys)
    {
        $values = array();
        foreach ($keys as $v) {
            $v = $this->sanitize($v);
            $item[$v] = $this->_cache->load($v);
            $values = $item;
        }
        return $values;
    }

    /**
     * Save values for a set of keys to cache
     *
     * @param array $keys   list of values to save
     * @param int   $expire expiration time
     * @return boolean true on success, false on failure
     */
    protected function write(array $keys, $expire=null)
    {
        $retour = false;
        foreach ($keys as $k => $v) {
            $k = $this->sanitize($k);
            $tmpReturn = $this->_cache->save($v, $k, array(), 3600);
            $retour = $retour && $tmpReturn;
        }
        return $retour;
    }

    /**
     * Remove values from cache
     *
     * @param array $keys list of keys to delete
     * @return boolean true on success, false on failure
     */
    protected function delete(array $keys)
    {
        $retour = false;
        foreach ($keys as $key) {
            $key = $this->sanitize($key);
            $tempReturn = $this->_cache->remove($key);
            $retour = $retour && $tempReturn;
        }
        return $retour;
    }

    /**
     * Remove *all* values from cache
     *
     * @return boolean true on success, false on failure
     */
    protected function purge()
    {
        return $this->_cache->clean();
    }

    /**
     * Par défaut, l'identifiant de cache contient le caractère #
     * Or, Zend_Cache n'accepte pas un identifiant de cache contenant #
     * On le remplace donc à la volée.
     *
     * @param type $string
     * @return string
     */
    protected function sanitize($string)
    {
        $string = parent::sanitize($string);
        return str_replace('#', '$$$', $string);
    }
}