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
class Xulub_Controller_Action_Helper_XbHeaders extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Constante des type autorisé pour le headerForceDownload
     *
     */
    private $_typeAutorise = array('xml', 'txt', 'csv', 'pdf');

    /**
     * Tableau de correspondance entre le type et le mime type
     * @todo : le ZF embarque surement les mime type et pourrait remplacer
     * ce tableau.
     */
    private $_tabMimeType = array(
        'xml' => 'text/xml',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'default' => 'application/octet-stream'
        );

    /**
     * Prépare les headers http pour envoyer un fichier au client et déclancher
     * la demande de téléchargement
     * @todo : renvoyer this
     *
     * @param string $type
     * @param string $outputFileName
     * @param integer $fileSize
     * @return Zend_Controller_Response_Abstract
     */
    public function headerForceDownload($type, $outputFileName, $fileSize)
    {
        if (!in_array($type, $this->_typeAutorise)) {
            $type = 'default';
        }

        ob_end_clean();
        $this->getResponse()->clearAllHeaders();
        $this->getResponse()->setHeader('Pragma', 'public', true)
            ->setHeader('Expires', '0')
            ->setHeader('Content-Length', $fileSize)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->setHeader('Cache-Control', 'private', false)
            ->setHeader('Content-Description', 'File Transfer')
            ->setHeader('Content-Type', $this->_tabMimeType[$type], true)
            ->setHeader('Content-Disposition', 'attachment; filename='.$outputFileName .';')
            ->setHeader('Content-Transfer-Encoding', 'binary');

        return $this->getResponse();
    }

    /**
     * Methode forcant le telechargement d'un fichier dont le type, le nom de
     * sortie et le chemin sont passés en paramètre.
     * @todo : renvoyer this
     *
     * @param string $type
     * @param string $outputFileName
     * @param string $pathFile
     */
    public function headerForceDownloadByFile($type, $outputFileName, $pathFile)
    {
        /**
         * @todo : on pourrait tout remplacer par un :
         * $this->headerForceDownloadByContent($type, $output_file_name, file_get_content($path_file));
         */
        if (file_exists($pathFile)) {

            $this->headerForceDownload(
                $type,
                $outputFileName,
                filesize($pathFile)
            )->setBody(file_get_contents($pathFile))->sendResponse();

        } else {
            throw new Xulub_Controller_Exception(
                'Impossible de mettre le fichier ' . $outputFileName .
                ' à disposition (fichier : ' . $pathFile
            );
        }
    }

    /**
     * Methode forcant le telechargement d'un contenu dont le type, le nom de
     * sortie  et le contenu sont passes en parametre.
     * @todo : renvoyer this
     *
     * @param string $type
     * @param string $outputFileName
     * @param string $content
     */
    public function headerForceDownloadByContent($type, $outputFileName,
        $content)
    {
        if (!empty($content)) {

            $this->headerForceDownload(
                $type,
                $outputFileName,
                strlen($content)
            )->setBody($content)->sendResponse();

        } else {
            throw new Xulub_Controller_Exception(
                'Impossible de mettre le fichier ' . $outputFileName .
                ' à disposition.'
            );
        }
    }
}