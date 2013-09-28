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
 * @package    Xulub_Mail
 * @uses Zend_Mail
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Mail extends Zend_Mail
{

    /**
     * bcc : adresses
     * @var array
     */
    protected $_bcc;

    /**
     * @var array
     * @static
     */
    protected static $_defaultBcc;

    /**
     * Sets Default bcc-email and name of the message
     *
     * @param  string               $email
     * @param  string    Optional   $name
     * @return void
     */
    public static function setDefaultBcc($email)
    {
        if (!is_array($email)) {
            $email = array($email);
        }
        self::$_defaultBcc = $email;
    }

    /**
     * Returns the default sender of the mail
     *
     * @return null|array   Null if none was set.
     */
    public static function getDefaultBcc()
    {
        return self::$_defaultBcc;
    }

    /**
     * Return l'objet Mail sous forme d'une chaine de caractère formattée par
     * le zend_Table_Text.
     * On ne loggue que les headers du mail (pas le body, question de volumétrie
     * sur la log)
     *
     * @return string
     */
    public function __toString()
    {
        $output = new Zend_Text_Table(
            array(
                'columnWidths' => array(10, 60),
                'decorator' => 'ascii'
            )
        );

        foreach ($this->getHeaders() as $key => $value) {
            $output->appendRow(array($key, $value[0]));
        }

        return $output->render();
    }


    /**
     * Sets Bcc-email based on the defaults
     *
     * @return Zend_Mail Provides fluent interface
     */
    public function setBccFromDefault()
    {
        $email = self::getDefaultBcc();

        if ($email === null) {

            require_once 'Zend/Mail/Exception.php';

            throw new Zend_Mail_Exception(
                'No default Bcc Address set to use'
            );
        }

        $this->addBcc($email);

        return $this;
    }


    /**
     * Extends Zend_Mail::send to put bcc default email
     *
     * Sends this email using the given transport or a previously
     * set DefaultTransport or the internal mail function if no
     * default transport had been set.
     *
     * @param  Zend_Mail_Transport_Abstract $transport
     * @return Zend_Mail                    Provides fluent interface
     */
    public function send($transport = null)
    {
        if (null === $this->_bcc && null !== self::getDefaultBcc()) {
            $this->setBccFromDefault();
        }

        return parent::send($transport);
    }
}