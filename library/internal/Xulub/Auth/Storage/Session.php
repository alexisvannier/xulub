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
 * @subpackage    Xulub_Session_Storage
 *
 * @desc Classe de stockage de Zend_Auth. Utilisation de Xulub_Session
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Auth_Storage_Session implements Zend_Auth_Storage_Interface
{
    /**
     * Variable de session utilisée par défaut
     */
    const MEMBER_DEFAULT = 'identity';

    /**
     * Variable de session où est stockée l'identifiant de l'utilisateur
     *
     * @var mixed
     */
    protected $_member;
    /**
     * Graine qui va être utilisée pour encodage l'identifiant de l'utilisateur
     * dans le cookie
     * @var <type>
     */
    protected $_salt = 'xulub-s4lt';

    /**
     * Variable de la session qui sera utilsiée pour stockée l'identifiant de
     * connexion
     *
     * @param string $member
     */
    public function __construct($member = self::MEMBER_DEFAULT)
    {
        $this->_member = $member;
    }

    /**
     * Retourne true si et seulement si le stockage est vide
     *
     * @throws Zend_Auth_Storage_Exception S'il est impossible de déterminer
     *                                     si le stockage est vide
     * @return boolean
     */
    public function isEmpty()
    {
        if (empty($_COOKIE[$this->_member])
            && !Xulub_Session::has($this->_member)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Retourne le contenu du stockage
     *
     * Comportement à définir si le stockage est vide.
     *
     * @throws Zend_Auth_Storage_Exception Si la lecture du stockage
     *                                     est impossible
     * @return mixed
     */
    public function read()
    {
        // Si le cookie est renseigné, on renvoie la valeur du cookie
        // Sinon, on prend la valeur de la session
        if (isset($_COOKIE[$this->_member])
            && !empty($_COOKIE[$this->_member])
        ) {
            return $this->_decrypt($_COOKIE[$this->_member]);
        } elseif (Xulub_Session::has($this->_member)) {
            return $this->_decrypt(Xulub_Session::get($this->_member));
        }
        return false;
    }

    /**
     * Ecrit $contents dans le stockage
     *
     * @param  mixed $contents
     * @throws Zend_Auth_Storage_Exception Si l'écriture de $contents
     *                                     est impossible
     * @return void
     */
    public function write($contents)
    {
        Xulub_Session::set($this->_member, $this->_encrypt($contents));
    }

    /**
     * RAZ du stockage (session et cookie)
     *
     * @throws Zend_Auth_Storage_Exception Si la remise à zéro (RAZ)
     *                                     est impossible
     * @return void
     */
    public function clear()
    {
        // On supprime le cookie en mettant une date d'expiration négative sur
        // le cookie
        //setcookie($this->_member, "", time() - 500000, '/');

        // suppression de la variable de session
        Xulub_Session::clear($this->_member);
    }

    /**
     * Fonction qui encrypte un texte
     * Si les méthodes mcryp_... sont présentes, elles seront utilisées
     * Sinon, on utilise un mécanisme de cryptage propre mais très faible !
     *
     * @param string $text
     * @return string string
     */
    protected function _encrypt($text)
    {
        if (function_exists('mcrypt_encrypt')
            && function_exists('mcrypt_decrypt')
        ) {
            return trim(
                base64_encode(
                    mcrypt_encrypt(
                        MCRYPT_RIJNDAEL_256,
                        $this->getSalt(),
                        $text,
                        MCRYPT_MODE_ECB,
                        mcrypt_create_iv(
                            mcrypt_get_iv_size(
                                MCRYPT_RIJNDAEL_256,
                                MCRYPT_MODE_ECB
                            ),
                            MCRYPT_RAND
                        )
                    )
                )
            );
        } else {
            return trim(str_rot13(base64_encode($text)));
        }
    }

    /**
     * Fonction qui décrypte un texte
     * Si les méthodes mcryp_... sont présentes, elles seront utilisées
     * Sinon, on utilise un mécanisme de cryptage propre mais très faible !
     *
     * @param string $text
     * @return string string
     */
    protected function _decrypt($text)
    {
        if (function_exists('mcrypt_encrypt')
            && function_exists('mcrypt_decrypt')
        ) {
            return trim(
                mcrypt_decrypt(
                    MCRYPT_RIJNDAEL_256,
                    $this->getSalt(),
                    base64_decode($text),
                    MCRYPT_MODE_ECB,
                    mcrypt_create_iv(
                        mcrypt_get_iv_size(
                            MCRYPT_RIJNDAEL_256,
                            MCRYPT_MODE_ECB
                        ),
                        MCRYPT_RAND
                    )
                )
            );
        } else {
            return trim(base64_decode(str_rot13($text)));
        }
    }

    /**
     * Définit la graine utilisée pour le cryptage
     *
     * @param string $salt
     * @return Authintranet_Auth_Storage_Xbsession
     */
    public function setSalt($salt)
    {
        $this->_salt = (string) $salt;
        return $this;
    }

    /**
     * Renvoie la graine
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->_salt;
    }
}