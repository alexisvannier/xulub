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
 * @package    Xulub_Log
 * @uses Zend_Log
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Log extends Zend_Log
{
    /*
    * Niveau d'erreur pour les logs
    */
    const NIV_LOG_EXCEPTION     = Zend_Log::ALERT;
    const NIV_LOG_APPLICATIVE   = Zend_Log::CRIT;
    const NIV_LOG_SQL           = Zend_Log::ERR;
    const NIV_LOG_AVERTISSEMENT = Zend_Log::WARN;
    const NIV_LOG_INFO          = Zend_Log::INFO;
    const NIV_LOG_DEBUG         = Zend_Log::DEBUG;

    /**
     * Méthode d'initialisation du log de l'application
     *
     * @return Zend_Log
     * @throws xbException si le log ne peut pas être créé
     */
    public function init($adapter = 'File', $niveauVerbosite = self::INFO)
    {
        try
        {
            // on formate le timestamp en une date plus lisible
            $this->setEventItem('timestamp', date("Y-m-d H:i:s"));

            if (isset($_SERVER['REMOTE_ADDR'])
                && isset($_SERVER['REMOTE_PORT'])
            ) {

                // on ajoute l'IP dans les informations à stocker dans le log
                $this->setEventItem(
                    'adr_ip',
                    $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT']
                );
            }

            // on prépare un formatteur pour la manière de logger les messages
            $formater = new Zend_Log_Formatter_Simple(
                '%timestamp% - %adr_ip% - %priorityName% (%priority%) : %message%' . PHP_EOL
            );

            $defaultLogDir = APPLICATION_PATH
            . DIRECTORY_SEPARATOR . 'share'
            . DIRECTORY_SEPARATOR . 'logs';

            switch($adapter)
            {
                case 'ZendMonitor' :
                    $writer = new Zend_Log_Writer_ZendMonitor();
                    break;

                case 'File' :
                        /**
                         * @todo : récupérer le répertoire de log via une
                         * variable de configuration
                         */
                    $writer = new Zend_Log_Writer_Stream(
                        $defaultLogDir . '/xulub.' . date('Ymd').'.log'
                    );
                    break;

                case 'Html' :
                    $debugStream = fopen("php://temp", "w+");
                    Zend_Registry::set('debug_stream', $debugStream);
                    $writer = new Zend_Log_Writer_Stream($debugStream);
                    // on formate la manière de logger les messages
                    $formater = new Zend_Log_Formatter_Simple(
                        '%timestamp% - %adr_ip% - %priorityName% (%priority%) : %message%<br />'
                    );
                    break;

                case 'Firebug' :
                    $writer = new Zend_Log_Writer_Firebug();
                    $request = new Zend_Controller_Request_Http();
                    $responseFirephp = new Zend_Controller_Response_Http();
                    $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
                    $channel->setRequest($request);
                    $channel->setResponse($responseFirephp);
                    Zend_Registry::set('response_firephp', $responseFirephp);
                    break;
            }

            $writer->addFilter(new Zend_Log_Filter_Priority($niveauVerbosite));
            $writer->setFormatter($formater);
            $this->addWriter($writer);
            return $this;
        }
        catch (Zend_Log_Exception $e)
        {
            throw new Zend_Exception(
                'Xulub_Log::init() : problème dans l\'initialisation du log ' . $e->getMessage()
            );
        }
    }

//    /**
//     * ajoute la chaine écrit la log vers la sortie
//     *
//     * @param string texte à logguer
//     * @param integer $niv_verbosit
//     * @deprecated au profil des méthodes warn, crit, alert, debug,...
//     * exemple : Zend_Registry::get('log')->warn('mon message');
//     */
//    public function writeLog($value, $niv_verbosit = self::INFO)
//    {
//        $this->log($value, $niv_verbosit);
//    }

    /**
     * Renvoit les messages vers le flux choisit s'ils étaient mis dans un
     * tampon
     * @todo : à virer
     *
     */
    public static function flushMessages()
    {
        if (Zend_Registry::isRegistered('response_firephp')) {

            $response = Zend_Registry::get('response_firephp');
            $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();

            // Envoi des données d'historisation vers le navigateur
            $channel->flush();
            $response->sendHeaders();
        }

        if (Zend_Registry::isRegistered('debug_stream')) {

            $debugStream = Zend_Registry::get('debug_stream');
            // read what we have written
            rewind($debugStream);
            $content = stream_get_contents($debugStream);

            $styles = <<<EOF
<style type="text/css">
#debug {
    margin:10px;
    border:1px solid red;
    background-color:white;
    color:black;
    padding-bottom:5px;
}

#debug h1{
    margin:0;
    padding:0;
    background-color:red;
    text-align:center;
    color:white;
}

#debug p{
    margin:5px;
}

#debug hr{
   color: red;
   background-color: red;
}
</style>
<div id="debug">
<h1>Débogage</h1>
<p>
EOF;

            $html  = $styles.PHP_EOL;
            $html .= $content;
            $html .= '</p>'.PHP_EOL;
            $html .= '</div>'.PHP_EOL;
            echo $html;
        }
    }
}