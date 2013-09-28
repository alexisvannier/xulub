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
 * @package Xulub_Controller
 * @subpackage Xulub_Controller_Action
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Controller_Action_Helper_XbPdf extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Contruit un PDF à partir d'une url et le renvoi au navigateur
     * @param string $media type de feuille de style à utiliser pour faire le
     * pdf
     * @param string $url lien du PDF à fabriquer
     */
    public function convertAndSend($media, $url, $fileName = '')
    {
        if ($url !== false) {
            /**
             * on bascule en http pour ne pas avoir de problème de certificats
             */
            $url = str_replace('https', 'http', $url);

            if (Zend_Registry::get('appli-config')->pdf->generate_with == 'prince') {

                // pour activer la génération via PrinceXML
                $pdfFile = tempnam('/tmp', 'prince');
                $this->xbpdfwriter(
                    $url,
                    $pdfFile,
                    $media,
                    Xulub_Session::getCookieJar()
                );
            } else {
                throw new Zend_Exception(
                    'L\'outil de génération de PDF n\'existe pas',
                    Zend_Log::ERR
                );
            }
            // on verifie la taille du pdf avant de le "servir"
            if (filesize($pdfFile) != 0) {

                if (empty($fileName)) {
                    $fileName = $this->_getPdfFilename($url);
                }
                $fileName = substr($fileName, 0, 255);

                $headers = $this->getActionController()->getHelperCopy(
                    'XbHeaders'
                );

                $headers->headerForceDownloadByFile(
                    'pdf',
                    $fileName,
                    $pdfFile
                );

                // on supprime le fichier
                unlink($pdfFile);
            } else {
                throw new Zend_Exception(
                    'La génération de PDF ne s\'est pas correctement effectuée',
                    Zend_Log::ERR
                );
            }
        } else {
            throw new Zend_Exception(
                'Vous devez passer une URL encodé en base64.',
                Zend_Log::ERR
            );
        }
    }

    /**
     * Fonction qui va retourner le nom du fichier PDF mis en forme
     * Split l'url pour extraire les noms du module, du composant et ajoute la
     * date
     *
     * @param string $url url du PDF généré
     */
    private function _getPdfFilename($url)
    {
        $tabUrl = explode('/', $url);

        $fileName = '';
        if (array_key_exists(5, $tabUrl)) {
            $fileName .= $tabUrl[5] . '_';
        }
        if (array_key_exists(6, $tabUrl)) {
            $fileName .= $tabUrl[6] . '_';
        }
        if (array_key_exists(8, $tabUrl)) {
            $fileName .= $tabUrl[8] . '-';
        }
        $fileName .= date('dmY') . '.pdf';

        return $fileName;
    }

    /**
     * Helper permettant de faciliter la génération de fichier PDF
     *
     * @todo à migrer
     *
     * @param string $url : url du PDF à générer
     * @param string $pdfFile : chemin vers le fichier PDF
     * @param string $media : media utilisé pour la génération PDF
     * @param string $cookieJarContent : contenu du cookie
     * @return bool
     */
    public function xbpdfwriter($url, $pdfFile, $media = 'print',
        $cookieJarContent = '')
    {
        // on libère la session pour le deuxième processus qui va être lancé
        // Utile si le PDF est généré à partir d'un contenu local
        // inutile dans le cas de la génération d'un PDF à partir d'une source
        //  HTML distante
        session_commit();

        try {
            $prince = new Xulub_Utils_Prince(
                Zend_Registry::get('appli-config')->pdf->prince->bin,
                $cookieJarContent
            );

            // Ajout du proxy :
            if (!empty(Zend_Registry::get('appli-config')->proxy->proxy_host)) {

                $proxyConfig = Zend_Registry::get('appli-config')->proxy->proxy_host;

                if (!empty(Zend_Registry::get('appli-config')->proxy->proxy_port)) {
                    $proxyConfig .= ':' . Zend_Registry::get('appli-config')->proxy->proxy_port;
                }

                $prince->setHttpProxy($proxyConfig);
            }

            // Modification propriete feuille de styles appelee :
            if (!empty($media)
                && in_array($media, array('screen', 'print', 'prince'))
            ) {
                $prince->setMedia($media);
            }

            return $prince->convertFileToFile($url, $pdfFile, $msg);

        } catch (Exception $e) {
            throw new Zend_Exception(
                'Problème dans génération PDF : ' . $e->getMessage(),
                Zend_Log::ERR
            );
        }
    }
}