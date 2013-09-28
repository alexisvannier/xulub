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
 * @package utils
 *
 * @desc Classe PHP d'interface avec PrinceXML. Largement inspiré de la classe
 * proposée par YesLogic Pty. Ltd (cf. http://www.princexml.com/download/accessories/)
 *
 * @author service informatique du CNFPT (inspiré de karibou - karibou.org)
 * @license GPL
 * @version $Id: xbPrince.class.php 781 2010-03-03 15:23:54Z vanniera $
 */
class Xulub_Utils_Prince
{
    /**
     * path bin
     *
     * @var string
     */
    private $_exePath;

    /**
     * Command line executed
     *
     * @var string
     */
    private $_pathAndArgs;

    /**
     * Stylesheets to apply
     *
     * @var string
     */
    private $_stylesheets;

    /**
     * Media type to apply
     *
     * @var string
     */
    private $_media;

    /**
     * ignore other css
     *
     * @var bool
     */
    private $_ignoreOtherCss;

    /**
     * is content html ?
     *
     * @var bool
     */
    private $_isHtml;

    /**
     * Specify the base URL of the input document.
     *
     * @var string
     */
    private $_baseUrl;

    /**
     * Disable XInclude processing.
     *
     * @var bool
     */
    private $_noXInclude;

    /**
     * Specify the username for HTTP authentication.
     *
     * @var string
     */
    private $_httpUser;

    /**
     * Specify the password for HTTP authentication.
     *
     * @var string
     */
    private $_httpPassword;

    /**
     * Log error messages to a file.
     *
     * @var string
     */
    private $_logFile;

    /**
     * Font embedding in PDF output.
     *
     * @var bool
     */
    private $_embedFonts;

    /**
     * compression of PDF output.
     *
     * @var bool
     */
    private $_compress;

    /**
     * Encrypt PDF output.
     *
     * @var bool
     */
    private $_encrypt;

    /**
     * encryption key size
     *
     * @var number
     */
    private $_encryptInfo;

    /**
     * contient le chemin vers le fichier cookie
     *
     * @var string
     */
    private $_cookieFile;

    /**
     * Cookie content
     *
     * @var string
     */
    private $_cookieJar;

    /**
     * Temp directory
     * @var string
     */
    private $_tempDir = '/tmp';

    /**
     * Proxy adress IP:PORT
     *
     * @var string
     */
    private $_httpProxy;

    public function __construct($exePath, $cookiejar = '', $tempdir = '/tmp')
    {
        if ( empty($exePath) || !is_executable($exePath) ) {
            throw new Zend_Exception(
                'APPS_OUTILS_PRINCE_ABSENT - Le binaire prince n\'est pas présent ou mal configuré.'
            );
        }

        $this->_exePath = $exePath;
        $this->_stylesheets = '';
        $this->_media = false;
        $this->_ignoreOtherCss = false;
        $this->_isHtml = false;
        $this->_baseUrl = '';
        $this->_noXInclude = true;
        $this->_httpUser = '';
        $this->_httpPassword = '';
        $this->_logFile = '';
        $this->_embedFonts = true;
        $this->_compress = true;
        $this->_encrypt = false;
        $this->_encryptInfo = '';
        $this->_cookieJar = $cookiejar;
        $this->_tempDir = $tempdir;

        if (!is_dir($this->_tempDir) && !is_writeable($this->_tempDir)) {
            throw new Exception('Temp dir not a directory or not writeable.');
        }

        $tmpCookieFile = tempnam($this->_tempDir, 'prince_cookie');
        file_put_contents($tmpCookieFile, $cookiejar);
        $this->_cookieFile = $tmpCookieFile;
    }

    public function __destruct()
    {
        if (is_file($this->_cookieFile)) {
            unlink($this->_cookieFile);
        }
    }

    public function getExePath()
    {
        return $this->_exePath;
    }

    public function getPathAndArgs()
    {
        return $this->_pathAndArgs;
    }

    /**
     * Add a CSS style sheet that will be applied to each document.
     * cssPath: The filename of the CSS style sheet.
     *
     * @param unknown_type $cssPath
     */
    public function addStyleSheet($cssPath)
    {
        $this->_stylesheets .= '-s "' . $cssPath . '" ';
    }

    /**
     * Clear all of the CSS style sheets.
     *
     */
    public function clearStyleSheets()
    {
        $this->_stylesheets = '';
    }

    /**
     * Specify which type of stylesheet documents should consider.
     * media: print, screen,...
     *
     * @param string $media
     */
    public function setMedia($media)
    {
        $this->_media = $media;
    }

    /**
     * Clear type of stylesheet documents should consider.
     *
     */
    public function clearMedia()
    {
        $this->_media = false;
    }

    /**
     * Specify whether documents should consider original stylesheet.
     * ignoreOtherCss: True if no original css should be considered.
     * @param unknown_type $ignoreOtherCss
     */
    public function setIgnoreOtherCss($ignoreOtherCss)
    {
        $this->_ignoreOtherCss = $ignoreOtherCss;
    }

    /**
     * Specify whether documents should be parsed as HTML or XML/XHTML.
     * html: True if all documents should be treated as HTML.
     *
     * @param bool $html
     */
    public function setHTML($html)
    {
        $this->_isHtml = $html;
    }

    /**
     * Specify a file that Prince should use to log error/warning messages.
     * logFile: The filename that Prince should use to log error/warning
     *   messages, or '' to disable logging.
     *
     * @param unknown_type $logFile
     */
    public function setLog($logfile)
    {
        $this->_logFile = $logfile;
    }

    /**
     * Specify the base URL of the input document.
     * base_url: The base URL or path of the input document, or ''.
     *
     * @param string $baseurl
     */
    public function setbase_url($baseurl)
    {
        $this->_baseUrl = $baseurl;
    }

    /**
     * Specify whether XML Inclusions (XInclude) processing should be applied
     * to input documents. XInclude processing will be performed by default
     * unless explicitly disabled.
     * xinclude: False to disable XInclude processing.
     *
     * @param unknown_type $xinclude
     */
    public function setXInclude($xinclude)
    {
        $this->_noXInclude = $xinclude;
    }

    /**
     * Specify a username to use when fetching remote resources over HTTP.
     * ser: The username to use for basic HTTP authentication.
     *
     * @param string $user
     */
    public function setHttpUser($user)
    {
        $this->_httpUser = $user;
    }

    /**
     * Specify a password to use when fetching remote resources over HTTP.
     * password: The password to use for basic HTTP authentication.
     *
     * @param string $password
     */
    public function setHttpPassword($password)
    {
        $this->_httpPassword = $password;
    }

    /**
     * Specify whether fonts should be embedded in the output PDF file. Fonts
     * will be embedded by default unless explicitly disabled.
     * embedFonts: False to disable PDF font embedding.
     *
     * @param unknown_type $embedfonts
     */
    public function setEmbedFonts($embedfonts)
    {
        $this->_embedFonts = $embedfonts;
    }


    /**
     * Specify whether compression should be applied to the output PDF file.
     * Compression will be applied by default unless explicitly disabled.
     * compress: False to disable PDF compression.
     *
     * @param bool $compress
     */
    public function setCompress($compress)
    {
        $this->_compress = $compress;
    }

    /**
     * Specify whether encryption should be applied to the output PDF file.
     * Encryption will not be applied by default unless explicitly enabled.
     * encrypt: True to enable PDF encryption.
     *
     * @param bool $encrypt
     */
    public function setEncrypt($encrypt)
    {
        $this->_encrypt = $encrypt;
    }

    /**
     * Set the parameters used for PDF encryption. Calling this method will
     * also enable PDF encryption, equivalent to calling setEncrypt(true).
     * keyBits: The size of the encryption key in bits (must be 40 or 128).
     * userPassword: The user password for the PDF file.
     * ownerPassword: The owner password for the PDF file.
     * disallowPrint: True to disallow printing of the PDF file.
     * disallowModify: True to disallow modification of the PDF file.
     * disallowCopy: True to disallow copying from the PDF file.
     * disallowAnnotate: True to disallow annotation of the PDF file.
     *
     * @param unknown_type $keybits
     * @param unknown_type $userPassword
     * @param unknown_type $ownerPassword
     * @param unknown_type $disallowPrint
     * @param unknown_type $disallowModify
     * @param unknown_type $disallowCopy
     * @param unknown_type $disallowAnnotate
     */
    public function setEncryptInfo($keybits, $userPassword, $ownerPassword,
        $disallowPrint = false, $disallowModify = false, $disallowCopy = false,
        $disallowAnnotate = false)
    {
        if ($keybits != 40 && $keybits != 128) {
            throw new Exception(
                "Invalid value for keyBits: $keybits (must be 40 or 128)"
            );
        }

        $this->_encrypt = true;

        $this->_encryptInfo =
        ' --key-bits ' . $keybits .
        ' --user-password="' . $userPassword .
        '" --owner-password="' . $ownerPassword . '" ';

        if ($disallowPrint) {
            $this->_encryptInfo .= '--disallow-print ';
        }

        if ($disallowModify) {
            $this->_encryptInfo .= '--disallow-modify ';
        }

        if ($disallowCopy) {
            $this->_encryptInfo .= '--disallow-copy ';
        }

        if ($disallowAnnotate) {
            $this->_encryptInfo .= '--disallow-annotate ';
        }
    }

    public function setCookieFile($file)
    {
        $this->_cookieFile = $file;
    }

    public function setHttpProxy($value)
    {
        $this->_httpProxy = $value;
    }


    /**
     * Convert an XML or HTML file to a PDF file.
     * The name of the output PDF file will be the same as the name of the
     * input file but with an extension of ".pdf".
     * xmlPath: The filename of the input XML or HTML document.
     * msgs: An optional array in which to return error and warning messages.
     * Returns true if a PDF file was generated successfully.
     *
     * @param unknown_type $xmlPath
     * @param unknown_type $msgs
     * @return unknown
     */
    public function convertFile($xmlPath, &$msgs = array())
    {
        $pathAndArgs = $this->getCommandLine();
        $pathAndArgs .= '"' . $xmlPath . '"';
        return $this->_convertInternalFileToFile($pathAndArgs, $msgs);
    }

    /**
     * Convert an XML or HTML file to a PDF file.
     * xmlPath: The filename of the input XML or HTML document.
     * pdfPath: The filename of the output PDF file.
     * msgs: An optional array in which to return error and warning messages.
     * Returns true if a PDF file was generated successfully.
     *
     * @param unknown_type $xmlPath
     * @param unknown_type $pdfPath
     * @param unknown_type $msgs
     * @return unknown
     */
    public function convertFileToFile($xmlPath, $pdfPath, &$msgs = array())
    {
        $pathAndArgs = $this->getCommandLine();
        $pathAndArgs .= '"' . $xmlPath . '" -o "' . $pdfPath . '"';

        if (!empty($this->_cookieFile)) {
            $pathAndArgs .= ' --cookiejar='.$this->_cookieFile;
        }

        return $this->_convertInternalFileToFile($pathAndArgs, $msgs);
    }

    /**
     * Convert an XML or HTML string to a PDF file, which will be passed
     * through to the output of the current PHP page.
     * xmlString: A string containing an XML or HTML document.
     * Returns true if a PDF file was generated successfully.
     *
     * @param unknown_type $xmlString
     * @return unknown
     */
    public function convertStringToPassthru($xmlString)
    {
        $pathAndArgs = $this->getCommandLine();
        $pathAndArgs .= '--silent -';
        return $this->_convertIinternalStringToPassthru(
            $pathAndArgs,
            $xmlString
        );
    }

    /**
     * Convert an XML or HTML string to a PDF file.
     * xmlString: A string containing an XML or HTML document.
     * pdfPath: The filename of the output PDF file.
     * msgs: An optional array in which to return error and warning messages.
     * Returns true if a PDF file was generated successfully.
     *
     * @param unknown_type $xmlString
     * @param unknown_type $pdfPath
     * @param unknown_type $msgs
     * @return unknown
     */
    public function convertStringToFile($xmlString, $pdfPath, &$msgs = array())
    {
        $pathAndArgs = $this->getCommandLine();
        $pathAndArgs .= ' - -o "' . $pdfPath . '"';
        return $this->_convertInternalStringToFile(
            $pathAndArgs,
            $xmlString,
            $msgs
        );
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    private function getCommandLine()
    {
        if ( empty($this->_exePath) || !is_executable($this->_exePath) ) {
            throw new Exception('exe path not present or not executable.');
        }

        $cmdline = $this->_exePath . ' ' . $this->_stylesheets;

        if ($this->_media) {
            $cmdline .= ' --media='.$this->_media.' ';
        }

        if ($this->_ignoreOtherCss) {
            $cmdline .= ' --no-author-style ';
        }

        if ($this->_isHtml) {
            $cmdline .= ' --input=html ';
        }

        if ($this->_baseUrl != '') {
            $cmdline .= " --base_url='" . $this->_baseUrl . "' ";
        }

        if ($this->_noXInclude == false) {
            $cmdline .= ' --no-xinclude ';
        }

        if ($this->_httpUser != '') {
            $cmdline .= " --http-user='" . $this->_httpUser . "' ";
        }

        if ($this->_httpPassword != '') {
            $cmdline .= " --http-password='" . $this->_httpPassword . "' ";
        }

        if ($this->_logFile != '') {
            $cmdline .= " --log='" . $this->_logFile . "' ";
        }

        if ($this->_embedFonts == false) {
            $cmdline .= ' --no-embed-fonts ';
        }

        if ($this->_compress == false) {
            $cmdline .= ' --no-compress ';
        }

        if ($this->_encrypt) {
            $cmdline .= ' --encrypt ' . $this->_encryptInfo;
        }

        if (!empty($this->_httpProxy)) {
            $cmdline .= ' --http-proxy='.$this->_httpProxy.' ';
        }

        return $cmdline;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $pathAndArgs
     * @param unknown_type $msgs
     * @return unknown
     */
    private function _convertInternalFileToFile($pathAndArgs, &$msgs)
    {
        $this->_pathAndArgs = $pathAndArgs;

        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $process = proc_open($pathAndArgs, $descriptorspec, $pipes);

        if (is_resource($process)) {
            $result = $this->_readMessages($pipes[2], $msgs);

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            proc_close($process);
            return ($result == 'success');
        } else {
            throw new Exception("Failed to execute $pathAndArgs");
        }
    }

    private function _convertInternalStringToFile($pathAndArgs, $xmlString,
        &$msgs)
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $process = proc_open($pathAndArgs, $descriptorspec, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);
            fclose($pipes[1]);

            $result = $this->_readMessages($pipes[2], $msgs);

            fclose($pipes[2]);

            proc_close($process);

            return ($result == 'success');
        } else {
            throw new Exception("Failed to execute $pathAndArgs");
        }
    }

    private function _convertIinternalStringToPassthru($pathAndArgs, $xmlString,
        &$msgs)
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $process = proc_open($pathAndArgs, $descriptorspec, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $xmlString);
            fclose($pipes[0]);
            fpassthru($pipes[1]);
            fclose($pipes[1]);

            $result = $this->_readMessages($pipes[2], $msgs);
            fclose($pipes[2]);

            proc_close($process);

            return ($result == 'success');
        } else {
            throw new Exception("Failed to execute $pathAndArgs");
        }
    }

    private function _readMessages($pipe, &$msgs)
    {
        while (!feof($pipe)) {
            
            $line = fgets($pipe);

            if ($line != false) {
                $msgtag = substr($line, 0, 4);
                $msgbody = rtrim(substr($line, 4));

                if ($msgtag == 'fin|') {
                    return $msgbody;
                } else if ($msgtag = 'msg|') {
                    $msg = explode('|', $msgbody, 4);

                    // $msg[0] = 'err' | 'wrn' | 'inf'
                    // $msg[1] = filename / line number
                    // $msg[2] = message text, trailing newline stripped

                    $msgs[] = $msg;
                } else {
                    // ignore other messages
                }
            }
        }

        return '';
    }
}