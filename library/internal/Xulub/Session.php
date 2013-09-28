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
     * @deprecated cette m�thode est d�pr�ci�e. Pour r�cup�rer le nom de la
     *  session, vous pouvez d�sormais utiliser session_name
     *
     * @return string
     */
    public function getName()
    {
        trigger_error(
            "La m�thode Xulub_Session::getName() ne doit plus �tre utilis�e",
            E_USER_DEPRECATED
        );

        return session_name();
    }

     /**
     * Fonction qui d�termine si l'internaute accepte les cookies ou non
     *
     * @deprecated Cette m�thode est obsol�te, doit �tre remplac�e mais par
      * quoi ?
     * @return boolean
     */
    public static function acceptCookie()
    {
        trigger_error(
            "La m�thode Xulub_Session::acceptCookie() ne doit plus �tre utilis�e.",
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
     * Ensemble de m�thodes permettant de s�curiser la session
     *  * on v�rifie qu'entre 2 requ�tes ayant le m�me identifiant de session,
     * le USER_AGENT reste identique. Si diff�rent, on supprime la session
     * cf. http://phpsec.org/projects/guide/4.html
     * On g�re l'exception du user_agent JAVA (utilis� pour g�n�rer le PDF)
     *
     */
    private static  function _securiseSession()
    {
        // on v�rifie la consistance de la session
        // Entre deux requ�tes, on doit conserver le m�me USER AGENT
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
                            'Le USER AGENT stock� en session a �t� alt�r�e.'
                        );
                    }
                }

                self::set('previous_user_agent', $_SERVER['HTTP_USER_AGENT']);
            }
        }
    }
//
//	/**
//	 * V�rifie que la session n'a pas expir�.
//	 * On g�re l'expiration applicativement pour pouvoir avvertir l'utilisateur
//	 * de la fin de sa session. Les m�canismes internes de php ne permettent pas
//	 * de pr�venir et rediriger l'utilisateur.
//	 * session en timeout =  temps courant + temps d�fini dans
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
     * Les variables de session sont s�rialis�es
     *
     * @param string $name nom de la variable de session
     * @param mixed $value valeur de la variable � sauvegard�e
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
     * Cette m�thode permet de g�nerer le contenu d'un cookiejar
     * cf. http://xiix.wordpress.com/2006/03/23/mozillafirefox-cookie-format/
     *
     * Ce cookie sera pass� � PrinceXML pour g�n�rer un PDF en mode connect�.
     *
     * Le format du cookie est le suivant :
     *
     *  Adresse Read Chemin Secure Expiration Nom Valeur
     *   - Adresse : Adresse du site.
     *   - Read : Est-ce que le cookie peut �tre lu par les autres machines du
     * m�me domaine ?     *   - Chemin : Chemin pour acc�der � la partie �
     * laquelle le cookie r�f�re. (g�n�ralement "/", la racine)
     *   - Secure : Est-ce que le cookie requiert une connexion s�curis�e ?
     *   - Expiration : Timestamp sp�cifiant quand le cookie est p�rim�.
     *   - Nom : Nom de la variable du cookie.
     *   - Valeur : Valeur sauvegard�e par le cookie.
     *
     * Chaque valeur est s�par�e par une tabulation.
     *
     * @return string
     */
    public static function getCookieJar()
    {
        // on r�cup�re les param�tres du cookie
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
     * Indique si une variable est s�rialis�e ou non
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
