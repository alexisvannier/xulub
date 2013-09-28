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
 * @package    Xulub_Session
 * @uses Zend_Session
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Session extends Zend_Session
{
    /**
     * Renvoie le nom de la session
     *
     * @deprecated cette méthode est dépréciée. Pour récupérer le nom de la
     *  session, vous pouvez désormais utiliser session_name
     *
     * @return string
     */
    public function getName()
    {
        trigger_error(
            "La méthode Xulub_Session::getName() ne doit plus être utilisée",
            E_USER_DEPRECATED
        );

        return session_name();
    }

     /**
     * Fonction qui détermine si l'internaute accepte les cookies ou non
     *
     * @deprecated Cette méthode est obsolète, doit être remplacée mais par
      * quoi ?
     * @return boolean
     */
    public static function acceptCookie()
    {
        trigger_error(
            "La méthode Xulub_Session::acceptCookie() ne doit plus être utilisée.",
            E_USER_DEPRECATED
        );

        if (defined('SID') && (SID != '')
            && (
                (isset($_COOKIE)
                && !count($_COOKIE))
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the named session variable exists.
     * @return boolean whether the named session variable exists
     */
    public static function has($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Ensemble de méthodes permettant de sécuriser la session
     *  * on vérifie qu'entre 2 requêtes ayant le même identifiant de session,
     * le USER_AGENT reste identique. Si différent, on supprime la session
     * cf. http://phpsec.org/projects/guide/4.html
     * On gère l'exception du user_agent JAVA (utilisé pour générer le PDF)
     *
     */
    private static  function _securiseSession()
    {
        // on vérifie la consistance de la session
        // Entre deux requêtes, on doit conserver le même USER AGENT
        // Si le User_Agent contient Prince (cas de PrinceXML) on ne supprime
        // pas la session
        if (isset($_SERVER['HTTP_USER_AGENT'])) {

            if ( stristr($_SERVER['HTTP_USER_AGENT'], 'prince') === false ) {

                if ( self::get('previous_user_agent')) {

                    if ($_SERVER['HTTP_USER_AGENT'] !== self::get('previous_user_agent')) {

                        self::destroy(); // destroy all data in session
                        throw new xbException(
                            'XULUB_SESSION_USER_AGENT_ALTERE',
                            xbException::NIV_EXCEPTION_SESSION,
                            'Le USER AGENT stocké en session a été altérée.'
                        );
                    }
                }

                self::set('previous_user_agent', $_SERVER['HTTP_USER_AGENT']);
            }
        }
    }
//
//	/**
//	 * Vérifie que la session n'a pas expiré.
//	 * On gère l'expiration applicativement pour pouvoir avvertir l'utilisateur
//	 * de la fin de sa session. Les mécanismes internes de php ne permettent pas
//	 * de prévenir et rediriger l'utilisateur.
//	 * session en timeout =  temps courant + temps défini dans
//	 appli.xml > temps courant
//	 *
//	 * @param integer
//	 * @return boolean
//	 */
//	private static function _isTimeOut($duree_expiration)
//	{
//		// on utilise $_SERVER['REQUEST_TIME] qui est plus rapide
//		// que time()
//		$request_time = (int)$_SERVER['REQUEST_TIME'];
//		if ( self::get('session_timeout') )
//		{
//			if ( self::get('session_timeout') < $request_time)
//			{
//				return true;
//			}
//		}
//		self::set('session_timeout', $request_time + $duree_expiration);
//		return false;
//	}

    /**
     * Renvoie la valeur de la variable de session $name
     *
     * Si $name est nul, on renvoie le tableau de session
     *
     * @param string nom de la variable de session
     * @return mixed
     */
    public static function get($name = '')
    {
        if ($name == '') {
            return $_SESSION;
        } elseif (isset($_SESSION[$name]) && is_string($_SESSION[$name])) {
            if ( self::isSerialized($_SESSION[$name]) ) {
                return unserialize($_SESSION[$name]);
            } else {
                return $_SESSION[$name];
            }
        }
        return null;
    }

    /**
     * Sauvegarde en session une valeur
     *
     * Les variables de session sont sérialisées
     *
     * @param string $name nom de la variable de session
     * @param mixed $value valeur de la variable à sauvegardée
     */
    public static function set($name = '', $value)
    {
        if ($name == '') {
            $_SESSION = $value;
        } else {
            $_SESSION[$name] = serialize($value);
        }
    }

    /**
     * Unsets a session variable.
     * @param string the session variable name
     */
    public static function clear($name)
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }

    /**
     *
     * Cette méthode permet de génerer le contenu d'un cookiejar
     * cf. http://xiix.wordpress.com/2006/03/23/mozillafirefox-cookie-format/
     *
     * Ce cookie sera passé à PrinceXML pour générer un PDF en mode connecté.
     *
     * Le format du cookie est le suivant :
     *
     *  Adresse Read Chemin Secure Expiration Nom Valeur
     *   - Adresse : Adresse du site.
     *   - Read : Est-ce que le cookie peut être lu par les autres machines du
     * même domaine ?     *   - Chemin : Chemin pour accéder à la partie à
     * laquelle le cookie réfère. (généralement "/", la racine)
     *   - Secure : Est-ce que le cookie requiert une connexion sécurisée ?
     *   - Expiration : Timestamp spécifiant quand le cookie est périmé.
     *   - Nom : Nom de la variable du cookie.
     *   - Valeur : Valeur sauvegardée par le cookie.
     *
     * Chaque valeur est séparée par une tabulation.
     *
     * @return string
     */
    public static function getCookieJar()
    {
        // on récupère les paramètres du cookie
        $cookieParams = session_get_cookie_params();

        // adresse :
        $cookie = $_SERVER['HTTP_HOST']."\t";

        // Read
        $cookie .= Xulub_Utils_Boolean::bool2str($cookieParams['httponly'])."\t";

        // Chemin
        $cookie .= $cookieParams['path']."\t";

        // Secure
        $cookie .= Xulub_Utils_Boolean::bool2str($cookieParams['secure'])."\t";

        // expiration
        $cookie .= $cookieParams['lifetime']."\t";

        $cookiejar = '';
        foreach ($_COOKIE as $key => $value) {
            $cookiejar .= $cookie;
            $cookiejar .= $key;
            $cookiejar .= "\t";
            $cookiejar .= $value;
            $cookiejar .= PHP_EOL;
        }
        return $cookiejar;
    }

    /**
     * Indique si une variable est sérialisée ou non
     *
     * @param mixed $data
     * @return boolean
    */
    public static function isSerialized($data)
    {
        if (trim($data) == "") {
            return false;
        }

        if (preg_match("/^(i|s|a|b|o|d|n)(:)(.*)/si", $data)) {
            return true;
        }

        return false;
    }
}
