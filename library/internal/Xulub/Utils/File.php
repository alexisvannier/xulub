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
 * @package    Xulub_Utils
 * @subpackage Xulub_Utils_File
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Utils_File
{
    public static $proxyAdress = '';
    public static $proxyPort = '';

    /**
     * Retourne l'extension du fichier file
     *
     * @param string $file
     */
    public static function getExtension($file)
    {
        $paramsFichier = pathinfo($file);
        // On récupère l'extension du fichier
        $extension = $paramsFichier['extension'];
        return $extension;
    }

    /**
     * Renvoie le contenu du fichier $src
     *
     * @param string $src
     * @return string
     */
    public static function getContent($src)
    {

        if (($fp = fopen($src, 'r')) === false) {
            //return 'An error occured while opening the file.';
            return false;
        } else {
            $content = '';
            while (!feof($fp)) {
                $content .= fread($fp, 4096);
            }
            fclose($fp);
            return $content;
        }
    }

    /**
     * Lit et sort sur la sortie standard un fichier afin d'eviter les memory limit
     *
     * @param string $filename
     * @return boolean
     */
    public static function readfile_chunked($filename)
    {
        $chunksize = 1 * (1024 * 1024);
        $buffer = '';
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            print $buffer;
            ob_flush();
        }
        return fclose($handle);
    }

    /**
     * Stocke la chaine $f_content
     * dans le fichier $f
     *
     * @param string $f
     * @param string $f_content
     * @return boolean
     */
    public static function putContent($f, $content)
    {
        //if (is_writable($f)) {
        if ($fp = fopen($f, 'w')) {
            fwrite($fp, $content, strlen($content));
            fclose($fp);
            return true;
        }
        //}
        return false;
    }

    /**
     * Définit si un fichier est supprimable ?
     *
     * @param string $f
     * @return boolean
     */
    public static function isDeletable($f)
    {
        if (is_file($f)) {
            return is_writable(dirname($f));
        } elseif (is_dir($f)) {
            return (is_writable(dirname($f)) && count(files::scandir($f)) <= 2);
        }
        return false;
    }

    /**
     * Copier d'un fichier binaire distant
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     */
    public static function copyRemote($src, $dest)
    {
        if (($fp1 = fopen($src, 'r')) === false) {
            //return 'An error occured while downloading the file.';
            return false;
        } else {
            if (($fp2 = fopen($dest, 'w')) === false) {
                fclose($fp1);
                //return __('An error occured while writing the file.');
                return false;
            } else {
                while (($buffer = fgetc($fp1)) !== false) {
                    fwrite($fp2, $buffer);
                }
                fclose($fp1);
                fclose($fp2);
                return true;
            }
        }
    }

    /**
     * Copie d'un fichier à travers un Proxy
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     */
    public static function copyRemoteProxy($src, $dest, $proxyAdress,
        $proxyPort)
    {

        self::$proxyAdress = $proxyAdress;
        self::$proxyPort = $proxyPort;

        $fp = fsockopen(self::$proxyAdress, self::$proxyPort);
        if (!$fp) {
            return false;
        }

        $request = "GET $src HTTP/1.0\r\n";
        $request .= "Accept-Charset: ISO-8859-1, utf-8;q=0.66, *;q=0.66\r\n";
        $request .= "Host: $proxyAdress\r\n\r\n";

        echo $request;
        fputs($fp, $request);

        $content = '';
        while (!feof($fp)) {
            $content .= fread($fp, 4096);
        }

        fclose($fp);
        $content = substr($content, strpos($content, "\r\n\r\n") + 4);

        if (($fp2 = fopen($dest, 'w')) === false) {
            //return __('An error occured while writing the file.');
            return false;
        } else {
            fwrite($fp2, $content);
            fclose($fp2);
            return true;
        }
    }

    /**
     * Suppression récursive d'un répertoire et de son contenu
     *
     * @param string $dir
     */
    public static function rmdirr($dir)
    {
        if ($objs = glob($dir . "/*")) {
            foreach ($objs as $obj) {
                is_dir($obj) ? self::rmdirr($obj) : unlink($obj);
            }
        }
    }

    /**
     * suppression d'un fichier
     *
     * @param string $file
     * @return bool
     */
    public static function rm($file)
    {
        if (is_file($file)) {
            return unlink($file);
        }
        return false;
    }

    /**
     * Si le répertoire n'existe pas, on le crée avec un accès rwxr-xr-x
     * Utilisé pour les fichiers temporaires Smarty
     *
     * @param string $dir
     */
    public Static function checkDirectory($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return true;
    }

    /**
     * Zip un fichier avec un commande système
     *
     * @param string $file
     * @return boolean
     */
    public static function gzip($file)
    {
        if (file_exists($file)) {

            $gzipCommand = escapeshellcmd(
                xbRegistry::get('appli-config')->gzip->bin
                . ' ' . $file
            );

            exec($gzipCommand, $output, $result);

            if ($result > 0) {
                echo "retour de commande gzip vide : "
                     . xbRegistry::get('appli-config')->gzip->bin . ' ' . $file;
                exit();
            }

            return true;
        }
        return false;
    }

    /**
     * Converts a CSV file to a simple XML file
     *
     * @param string $file
     * @param string $container
     * @param string $rows
     * @param string $delimiter
     * @return string
     */
    public static function csv2xml($file, $container = 'data', $rows = 'row',
        $delimiter = ';')
    {
        $r = "<{$container}>\n";
        $row = 0;
        $cols = 0;
        $titles = array();

        $handle = fopen($file, 'r');
        if (!$handle) {
            return $handle;
        }

        while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {

            if ($row > 0) {
                $r .= "\t<{$rows}>\n";
            }

            if (!$cols) {
                $cols = count($data);
            }

            for ($i = 0; $i < $cols; $i++) {

                if ($row == 0) {
                    $titles[$i] = $data[$i];
                    continue;
                }

                // on fait un isset pour protégé d'une ligne de données
                // malformée (- de colonne de données que de colonne d'entête)
                if (isset($data[$i])) {
                    $r .= "\t\t<{$titles[$i]}>";
                    $r .= $data[$i];
                    $r .= "</{$titles[$i]}>\n";
                }
            }

            if ($row > 0) {
                $r .= "\t</{$rows}>\n";
            }
            $row++;
        }
        fclose($handle);
        $r .= "</{$container}>";

        return utf8_encode($r);
    }

    /**
     * Recherche de manière récursive les fichiers respectant le $pattern.
     * Largement inspiré d'un commentaire présent dans
     * http://fr.php.net/manual/fr/function.glob.php
     *
     * @param string $pattern pattern passé à la glob glob
     * @param int $flags drapeaux transmis à la fonction glob
     * @param string $path répertoire qui sera scanné de manière récursive
     * @return array tableau de fichiers répondant aux patterns
     */
    public static function rglob($pattern, $flags = 0, $path = '')
    {
        if (empty($pattern)) {
            return array();
        } else {
            if (($dir = dirname($pattern)) != '.') {
                if ($dir == DIRECTORY_SEPARATOR) {
                    $dir = '';
                }
                return self::rglob(
                    basename($pattern),
                    $flags,
                    $dir . DIRECTORY_SEPARATOR
                );
            }
            $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
            $files = glob($path . $pattern, $flags);
            foreach ($paths as $p) {
                $files = array_merge(
                    $files,
                    self::rglob($pattern, $flags, $p . DIRECTORY_SEPARATOR)
                );
            }
            return $files;
        }
    }

    /*
      A tester à la place de la fonction gzip.
      Necessite de compiler php5 :
      "Pour pouvoir utiliser ces fonctions, vous devez compiler PHP avec le
     * support ZIP en utilisant l'option de configuration --enable-zip.
     * Ceci ne nécessite pas de bibliothèque externe."
      http://fr3.php.net/manual/fr/ref.zip.php

      public static function phpzip($file)
      {
      $zip = new ZipArchive();
      $filename = "./test112.zip";

      if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
      exit("cannot open <$filename>\n");
      }

      $zip->addFile($file);
      $zip->close();
      }
     */
}

