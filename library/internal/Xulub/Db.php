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
 * @package    Xulub_Db
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Db
{

    /**
     * Contructeur
     *
     * @deprecated
     * @param string $driver
     * @param array $params
     * @return Xulub_Db
     */
    public function __construct($driver, $params)
    {
        trigger_error(
            "La m�thode Xulub_Db::__construct() ne doit plus �tre utilis�e.",
            E_USER_DEPRECATED
        );
    }

    /**
     * Change la connexion courante dans le pool de connexion � la bdd
     *
     * @deprecated
     * @param string
     * @exception Xulub_Db_Exception si la connexion n'existe pas
     */
    public function setCurrentConnectionName($name)
    {
        trigger_error(
            "La m�thode Xulub_Db::setCurrentConnectionName() ne doit plus
�tre utilis�e. A remplacer par
Zend_Registry::get('multidb')->setCurrentConnection()",
            E_USER_DEPRECATED
        );
        Zend_Registry::get('multidb')->setCurrentConnection($name);
    }

    /**
     * R�cup�re le nom de la connexion courante dans le pool de connexion
     *
     * @deprecated
     * @todo voir comment r�cup�rer le nom
     * @return string
     */
    public function getCurrentConnectionName()
    {
        trigger_error(
            "La m�thode Xulub_Db::getCurrentConnectionName() ne doit plus
�tre utilis�e. A remplacer par
Zend_Registry::get('multidb')->getCurrentConnectionName()",
            E_USER_DEPRECATED
        );
        return Zend_Registry::get('multidb')->getCurrentConnectionName();
    }

    /**
     * Retourne une cha�ne de connexion sql du pool.
     *
     * @deprecated
     * @param string $connexion_name nom de la connexion
     * @return string
     */
    public function getDsn($name = null)
    {
        trigger_error(
            "La m�thode Xulub_Db::getDsn() ne doit plus �tre utilis�e. " .
            "A remplacer par Zend_Registry::get('multidb')->getDsn()",
            E_USER_DEPRECATED
        );
        return Zend_Registry::get('multidb')->getDsn($name);
    }

    /**
     * Retourne l'objet de connexion courant � la base de donn�es
     *
     * @deprecated
     * @return Zend_Db
     */
    public function getCurrentConnection()
    {
        trigger_error(
            "La m�thode Xulub_Db::getCurrentConnectionName()
                ne doit plus �tre utilis�e.",
            E_USER_DEPRECATED
        );
        return Zend_Registry::get('multidb')->getDb();
    }

    /**
     * Change le format de date pour la session (au sens oracle/sql) courante
     *
     * @deprecated
     * @param Zend_Db $db
     * @param string $dateFormat
     */
    public function changeNlsDateFormat($db, $dateFormat)
    {
        trigger_error(
            "La m�thode Xulub_Db::changeNlsDateFormat() ne doit plus �tre
                utilis�e.",
            E_USER_DEPRECATED
        );
    }

    /**
     * Ajoute une connexion au pool de connexion
     * Si une connection portant ce nom existe d�j�, on lance une exception
     *
     * @deprecated
     * @param Zend_Db $db
     * @param string $connexion_name
     */
    public function addDbConnection($db, $name)
    {
        trigger_error(
            "La m�thode Xulub_Db::addDbConnection() ne doit plus �tre utilis�e.
                Elle doit �tre remplac�e par
                Zend_Registry::get('multidb')->setDb().",
            E_USER_DEPRECATED
        );
        Zend_Registry::get('multidb')->setDb($name, $db);
    }

    /**
     * Instancie une nouvelle connection � la base de donn�es et l'ajoute au
     * pool de connexion. Change le format de date de la session en prenant
     * celui d�finit dans le fichier de configuration.
     * Si la param�tre $is_current est activ�, la connexion deviendra la
     * connexion courante pour l'ex�cution des prochaine requ�tes
     *
     * @deprecated
     *
     * @param string $driver
     * @param array $params
     * @param string $connexionName
     * @param bool�en $isCurrent
     */
    public function createConnection($driver, $params, $connexionName,
        $isCurrent = false)
    {
        trigger_error(
            "La m�thode Xulub_Db::createConnection() ne doit plus �tre
                utilis�e.",
            E_USER_DEPRECATED
        );
    }

    /**
     * Ferme la connexion et la supprime du pool de connexion
     *
     * @deprecated
     * @param string $connexionName
     */
    public function removeConnection($connexionName)
    {
        trigger_error(
            "La m�thode Xulub_Db::removeConnection() ne doit plus �tre
                utilis�e.",
            E_USER_DEPRECATED
        );
    }

    /**************************************************************************
               Execution des requetes SQL
    ***************************************************************************/

    /**
     * Execute une requete et retourne un tableau de valeur
     *
     * @param string $query
     * @param array $params
     * @param booleen $useCache
     * @return array
     */
    public static function execGetArray($query, $params = array(),
        $useCache = false)
    {
        try {
            $result = false;

            if (empty($params)) {
                $params = array();
            }

            if ($useCache) {

                $cache = Zend_Registry::get('cachemanager')->getCache(
                    'database'
                );

                $id = md5('db'.'-'.$query.'-'.serialize($params));

                $result = $cache->load($id);

                if ($result === false) {
                    $db = Zend_Registry::get('multidb')->getDb();
                    $result = $db->fetchAll($query, $params);
                    $cache->save($result, $id, array('sql'));
                }
            } else {
                $db = Zend_Registry::get('multidb')->getDb();
                $result = $db->fetchAll($query, $params);
            }
            return $result;
        } catch(Zend_DB_Exception $e) {
            $debugMsg =
                    'erreur    :'.$e->getMessage().PHP_EOL.
                    'connexion :'.Zend_Registry::get('multidb')->getCurrentConnectionName().PHP_EOL.
                    'query     :'.$query.PHP_EOL.
                    'parameters:'.str_replace(PHP_EOL, '', var_export($params, true)).PHP_EOL;
            throw new Xulub_Db_Exception(
                'erreur dans la requ�te : '.$debugMsg, 'Xulub_Db_PB_EXEC_GET_ARRAY'
            );
        }
    }


    public static function execGetRow($query, $params = array(),
        $useCache = false)
    {
        try {
            $result = false;

            if (empty($params)) {
                $params = array();
            }

            if ($useCache) {

                $cache = Zend_Registry::get('cachemanager')->getCache(
                    'database'
                );

                $id = md5('db'.'-'.$query.'-'.serialize($params));

                $result = $cache->load($id);

                if ($result === false) {
                    $db = Zend_Registry::get('multidb')->getDb();
                    $result = $db->fetchRow($query, $params);
                    $cache->save($result, $id, array('sql'));
                }
            } else {
                $db = Zend_Registry::get('multidb')->getDb();
                $result = $db->fetchRow($query, $params);
            }

            if (empty($result)) {
                $result = array();
            }

            return $result;

        } catch(Zend_DB_Exception $e) {

            $debugMsg =
                    'erreur    :'.$e->getMessage().PHP_EOL.
                    'connexion :'.Zend_Registry::get('multidb')->getCurrentConnectionName().PHP_EOL.
                    'query     :'.$query.PHP_EOL.
                    'parameters:'.str_replace(PHP_EOL, '', var_export($params, true)).PHP_EOL;

            throw new Xulub_Db_Exception(
                'erreur dans la requ�te : '.$debugMsg, 'Xulub_Db_PB_EXEC_GET_ROW'
            );
        }
    }

    public static function execGetOne($query, $params = array(),
        $useCache = false)
    {
        try {

            $result = false;

            if (empty($params)) {
                $params = array();
            }

            if ($useCache) {

                $cache = Zend_Registry::get('cachemanager')->getCache(
                    'database'
                );

                $id = md5('db'.'-'.$query.'-'.serialize($params));

                $result = $cache->load($id);

                if ($result === false) {
                    $db = Zend_Registry::get('multidb')->getDb();
                    $result = $db->fetchOne($query, $params);
                    $cache->save($result, $id, array('sql'));
                }
            } else {
                $db = Zend_Registry::get('multidb')->getDb();
                $result = $db->fetchOne($query, $params);
            }
            return $result;
        } catch(Zend_DB_Exception $e) {

            $debugMsg =
                    'erreur    :'.$e->getMessage().PHP_EOL.
                    'connexion :'.Zend_Registry::get('multidb')->getCurrentConnectionName().PHP_EOL.
                    'query     :'.$query.PHP_EOL.
                    'parameters:'.str_replace(PHP_EOL, '', var_export($params, true)).PHP_EOL;

            throw new Xulub_Db_Exception(
                'erreur dans la requ�te : '.$debugMsg, 'Xulub_Db_PB_EXEC_GET_ONE'
            );
        }
    }

    /**
     * Appel d'une proc�dure stock�e. On force le type des param�tres de sorties
     * � une cha�ne de 4000 caract�res (compatibilit� avec l'ancienne librairie
     * adodb).
     *
     * @param string $procDef
     * @param array $in
     * @param array $out
     * @return array
     */
    public static function callProc($procDef, $in = array(), $out = array())
    {
        try {
            $db = Zend_Registry::get('multidb')->getDb();

            $statement = $db->prepare($procDef);

            // binding du tableau des param�tres IN
            foreach ($in as $param => $value) {
                $statement->bindParam($param, $in[$param]);
            }

            // binding du tableau des param�tres OUT
            foreach ($out as $key => $value) {
                $statement->bindParam($key, $out[$key], SQLT_CHR, 4000);
            }

            if (!$statement->execute()) {
                return false;
            }
            return $out;
        }
        catch(Zend_DB_Exception $e) {
            $debugMsg =
                    'erreur    :'.$e->getMessage().PHP_EOL.
                    'connexion :'.Zend_Registry::get('multidb')->getCurrentConnectionName().PHP_EOL.
                    'query     :'.$procDef.PHP_EOL.
                    'in        :'.str_replace(PHP_EOL, '', var_export($in, true)).PHP_EOL.
                    'out       :'.str_replace(PHP_EOL, '', var_export($out, true)).PHP_EOL;
            throw new Xulub_Db_Exception(
                'erreur dans l\'appel de la proc�dure : '.$debugMsg, 'Xulub_Db_PB_EXEC_call_procedure'
            );
        }
    }

    /**
     * On utilise le hack de adodb pour assurer la compatibilit�. Si on "pagine"
     * depuis le premier enregistrement rownum.
     * Attention les param�tres start et nb_rows sont invers�s par rapport � la
     * classe Xulub_DbExecute
     * @param string $query
     * @param array $params
     * @param integer $nbRows
     * @param integer $startRow
     * @param boolean $useCache
     * @return array
     */
    public static function execSelectLimit($query, $params = false,
        $nbRows = 10, $startRow = 0, $useCache = false)
    {
        try {
            if ($nbRows < 1) {
                return array();
            }

            if ($startRow === 0) {
                $queryLimit = 'select * from ('. $query .') where rownum <= :xb_number_rows';
                $params['xb_number_rows'] = $nbRows;
            } else {
                // on fait un select d'un row pour avoir la liste des colonnes
                // et ins�rer le rownum dans le select
                $colonnesName = self::execGetRow(
                    "SELECT * FROM (".$query.") WHERE rownum=1",
                    $params,
                    $useCache
                );

                if (count($colonnesName)) {
                    $colonnesName = array_keys($colonnesName);
                    $listeFields = implode(',', $colonnesName);
                    array_push($colonnesName, 'rownum as XULUB_LIMIT_ROWNUM');
                    $listeFieldsAndRownum = implode(',', $colonnesName);
                }

                // on ne touche pas � la requ�tue d'origine, on l'encapsule en
                // ajoutant un rownum. et on encapsule le tout dans un autre
                // select pour filtrer par rownum.
                $queryLimit = 'select ' . $listeFields . ' from (select '
                    . $listeFieldsAndRownum.' from ('.$query.')) '
                    . 'where XULUB_LIMIT_ROWNUM between '
                    . ':xb_start_row and :xb_number_rows';

                // on ajoute les param�tres du limite en utilisant le rownum
                // (sp�cifique oracle)
                $params['xb_start_row'] = abs($startRow) + 1;
                $params['xb_number_rows'] = abs($startRow) + abs($nbRows);

            }

            return self::execGetArray($queryLimit, $params, $useCache);

        } catch(Zend_DB_Exception $e) {

            $debugMsg =
                    'erreur    :'.$e->getMessage().PHP_EOL.
                    'connexion :'.Zend_Registry::get('multidb')->getCurrentConnectionName().PHP_EOL.
                    'query     :'.$query.PHP_EOL.
                    'parameters:'.var_export($params, true).PHP_EOL;

            throw new Xulub_Db_Exception(
                'erreur dans l\'appel de la proc�dure : '.$debugMsg, 'Xulub_Db_PB_EXEC_SELECT_LIMIT'
            );
        }
    }

    /**
     * Function transformant les valeurs not null mais etant chaine vide par -1
     * afin de diff�rencier la chaine vide de NULL dans Oracle
     * Attention il faut alors traiter le cas -1 dans la procedure Oracle.
     *
     * Parcours tous les �l�ments du tableau afin de construit un nouveau
     * tableau trait�.
     *
     * @param array $array
     * @return array
     */
    public static function emptyStringNullFromArray($array)
    {
        foreach ($array as $key => $value) {

            if ($value == ''
                && !is_null($value)
                && stripos($key, 'DAT') === false
            ) {
                $value = '-1';
            }

            $newArray[$key] = $value;
        }
        return $newArray;
    }

    /**
     * Fonction qui convertit un tableau SQL
     * en un tableau pour QuickForm :
     * exemple : $t[<cle>] = <valeur>
     *
     * $data est le tableau SQL
     * $key : colonne que nous allons utiliser comme cl�
     * $libelle : colonne que nous allons utiliser comme valeur
     *
     * @todo � migrer
     *
     * @param array $data
     * @param string $key
     * @param string $libelle
     * @return array
     */
    public static function SqlArrayToQuickformArray($data, $key, $libelle,
        $defaultData = array('-' => '-'))
    {
        $output = $defaultData;

        if (count($data)) {

            foreach ($data as $item) {

                if (isset($item[$key]) && isset($item[$libelle])) {

                    $outputKey = $item[$key];
                    $outputLib = $item[$libelle];
                    $output[$outputKey] = $outputLib;
                }
            }
        }
        return $output;
    }
}