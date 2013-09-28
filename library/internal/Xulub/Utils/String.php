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
 * @subpackage Xulub_Utils_String
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
class Xulub_Utils_String
{

    /**
     * Fonction qui prend en entrée une chaine accentée
     * renvoie une chaine sans accents
     *
     * @param string $chaine
     * @return string
     */
    public static function supprimeAccents($chaine)
    {
        $a = "àáâãäåòóôõöøèéêëçìíîïùúûüÿñ";
        $b = "aaaaaaooooooeeeeciiiiuuuuyn";
        return (strtr($chaine, $a, $b));
    }

    /**
     * Fonction qui prend en entrée une chaine accentée
     * et renvoie une chaine sans accents et en minucule
     *
     * @param string $chaine
     * @return string
     */
    public static function supprimeAccentsMinuscule($chaine)
    {
        $chaine = self::supprimeAccents(strtolower($chaine));
        return $chaine;
    }

    /**
     * Accents replacement
     *
     * Replaces some occidental accentuated characters by their ASCII
     * representation.
     *
     * Text utilities
     *
     * @package Clearbricks
     * @subpackage Common
     *
     * @param    string    $str        String to deaccent
     * @return    string
     */
    public static function deaccent($str)
    {
        $pattern['A'] = '\x{00C0}-\x{00C5}';
        $pattern['AE'] = '\x{00C6}';
        $pattern['C'] = '\x{00C7}';
        $pattern['D'] = '\x{00D0}';
        $pattern['E'] = '\x{00C8}-\x{00CB}';
        $pattern['I'] = '\x{00CC}-\x{00CF}';
        $pattern['N'] = '\x{00D1}';
        $pattern['O'] = '\x{00D2}-\x{00D6}\x{00D8}';
        $pattern['OE'] = '\x{0152}';
        $pattern['S'] = '\x{0160}';
        $pattern['U'] = '\x{00D9}-\x{00DC}';
        $pattern['Y'] = '\x{00DD}';
        $pattern['Z'] = '\x{017D}';

        $pattern['a'] = '\x{00E0}-\x{00E5}';
        $pattern['ae'] = '\x{00E6}';
        $pattern['c'] = '\x{00E7}';
        $pattern['d'] = '\x{00F0}';
        $pattern['e'] = '\x{00E8}-\x{00EB}';
        $pattern['i'] = '\x{00EC}-\x{00EF}';
        $pattern['n'] = '\x{00F1}';
        $pattern['o'] = '\x{00F2}-\x{00F6}\x{00F8}';
        $pattern['oe'] = '\x{0153}';
        $pattern['s'] = '\x{0161}';
        $pattern['u'] = '\x{00F9}-\x{00FC}';
        $pattern['y'] = '\x{00FD}\x{00FF}';
        $pattern['z'] = '\x{017E}';

        $pattern['ss'] = '\x{00DF}';

        foreach ($pattern as $r => $p) {
            $str = preg_replace('/[' . $p . ']/u', $r, $str);
        }

        return $str;
    }

    /**
     * String to URL
     *
     * Transforms a string to a proper URL.
     *
     * Text utilities
     *
     * @package Clearbricks
     * @subpackage Common
     *
     * @param string    $str            String to transform
     * @param boolean    $withSlashes    Keep slashes in URL
     * @return string
     */
    public static function str2URL($str, $withSlashes=true)
    {
        $str = self::deaccent($str);
        $str = preg_replace('/[^A-Za-z0-9_\s\'\:\/[\]-]/', '', $str);

        return self::tidyURL($str, $withSlashes);
    }

    /**
     * URL cleanup
     *
     * Text utilities
     *
     * @package Clearbricks
     * @subpackage Common
     *
     * @param string    $str            URL to tidy
     * @param boolean    $keepSlashes    Keep slashes in URL
     * @param boolean    $keepSpaces    Keep spaces in URL
     * @return string
     */
    public static function tidyURL($str, $keepSlashes=true, $keepSpaces=false)
    {
        $str = strip_tags($str);
        $str = str_replace(
            array('?', '&', '#', '=', '+', '<', '>', '"', '%'),
            '',
            $str
        );
        $str = str_replace("'", ' ', $str);
        $str = preg_replace('/[\s]+/', ' ', trim($str));

        if (!$keepSlashes) {
            $str = str_replace('/', '-', $str);
        }

        if (!$keepSpaces) {
            $str = str_replace(' ', '-', $str);
        }

        $str = preg_replace('/[-]+/', '-', $str);

        # Remove path changes in URL
        $str = preg_replace('%^/%', '', $str);
        $str = preg_replace('%\.+/%', '', $str);

        return $str;
    }

    /**
     * Enlève la ponctuation et les caractères accentués
     *
     * @param string $text
     * @param string $fromEnc UTF-8
     * @return string
     */
    public static function to7bit($text, $fromEnc)
    {

        $text = mb_convert_encoding($text, 'HTML-ENTITIES', $fromEnc);

        $text = preg_replace(
            array(
                '/&szlig;/',
                '/&(..)lig;/',
                '/&([aouAOU])uml;/',
                '/&(.)[^;]*;/'
            ),
            array(
                'ss',
                "$1",
                "$1" .
                'e',
                "$1"
            ),
            $text
        );

        $outText = eregi_replace("[^a-z0-9]", '', $text);

        return $outText;
    }

    /**
     * Traduit une chaine alphanumérique en décomposant chaque lettre ou chiffre
     * Utilisé pour éviter la confusion dans un mot de passe entre 1 et l par
     * exemple
     *
     * @param string $chaine
     * @return array
     */
    public static function epellationChaine($chaine)
    {
        $traduction = array();

        $chaineAParcourir = str_split($chaine);

        $prononciationLettre = array(
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
            'e' => 'e',
            'f' => 'f',
            'g' => 'g',
            'h' => 'h',
            'i' => 'i',
            'j' => 'j',
            'k' => 'k',
            'l' => 'ell',
            'm' => 'm',
            'n' => 'n',
            'o' => 'o',
            'p' => 'p',
            'q' => 'q',
            'r' => 'r',
            's' => 's',
            't' => 't',
            'u' => 'u',
            'v' => 'v',
            'w' => 'w',
            'x' => 'x',
            'y' => 'y',
            'z' => 'z');

        $prononciationChiffre = array(
            '0' => 'zéro',
            '1' => 'un',
            '2' => 'deux',
            '3' => 'trois',
            '4' => 'quatre',
            '5' => 'cinq',
            '6' => 'six',
            '7' => 'sept',
            '8' => 'huit',
            '9' => 'neuf');

        foreach ($chaineAParcourir as $lettre) {

            // si c'est un chiffre
            if (ctype_digit($lettre)) {
                $traduction[] = $lettre . ' - le chiffre ' . $prononciationChiffre[$lettre];
            } elseif (ctype_alpha($lettre)) {
                // si c'est une lettre, on distingue majuscule et minuscule
                $casse = '';

                if (ctype_lower($lettre)) {

                    $casse = 'minuscule';

                } elseif (ctype_upper($lettre)) {

                    $casse = 'majuscule';

                }

                $traduction[] = $lettre . ' - la lettre ' . $casse . ' "' . $prononciationLettre[strtolower($lettre)] . '"';
            } else {
                // Si ce n'est pas du alphanumérique
                // on gère de manière particulière l'espace
                if ($lettre === ' ') {
                    $traduction[] = '  - le caractère espace " "';
                } else {
                    $traduction[] = $lettre . ' - le caractère ' . $lettre;
                }
            }
        }

        if (count($traduction) == 0) {
            $traduction[] = 'La chaîne est vide.';
        }

        return $traduction;
    }

    /**
     * Convertit les valeurs en bdd en booleen
     * Essentiellement utile pour les valeurs O et N
     *
     * @todo : voir pour son remplacement en Zend_Filter::filterStatic(
     * $payante,
     * 'Boolean'
     * );
     *
     * @param char|boolean $value
     * @return bool
     */
    public static function stringToBooleen($value)
    {
        switch ($value)
        {
            case 'O' : return(true);
            case 'N' : return(false);

            case 'o' : return(true);
            case 'n' : return(false);

            case '1' : return(true);
            case '0' : return(false);

            case 'true' : return(true);
            case 'false' : return(false);
        }
        return null;
    }

    /**
     * Fonction de génération d'un mot de passe. Prend en paramètre une longueur
     * de caractère pour la chaine à générer.
     * On peut également ajouter des caractères spéciaux.
     *
     * @param integer $length
     * @param false|integer $useSpecCar
     * @return string
     */
    public static function generateRandomString($length, $useSpecCar = false)
    {
        $pass = '';

        // A List of vowels and vowel sounds that we can insert in
        // the password string
        $vowels = array(
            "a", "e", "i", "o", "u",
            "ae", "ou", "io","ea", "ia", "ai", "au", "eu", "oi"
        );

        $numbers = array("1", "2","3","4","5","6","7","8","9","0");

        // A List of Consonants and Consonant sounds that we can insert
        // into the password string
        $consonants = array(
            "b", "c", "d", "f", "g", "h", "j", "k", "l", "m",
            "n", "p", "r", "s", "t", "v", "w", "z",
            "tr", "cr", "fr", "dr", "gr", "wr", "pr",
            "th", "ch", "ph", "st", "sl", "cl", "dl", "kl", "fl", "pl", "gu",
            "on"
        );

        $specials = array("!", "$", "*", ".", ";", "-", "_", ",");

        // For the call to rand(), saves a call to the count() function
        // on each iteration of the for loop
        $vowelCount = count($vowels);
        $consonantCount = count($consonants);
        $numberCount = count($numbers);
        $specialCount = count($specials);

        // From $i .. $length, fill the string with alternating consonant
        // vowel pairs.
        for ($i = 0; $i < $length; ++$i) {

            $pass[] = $consonants[rand(0, $consonantCount - 1)].
            $vowels[rand(0, $vowelCount - 1)].
            $numbers[rand(0, $numberCount - 1)];
        }

        // Intègre des caractères spéciaux dans le mot de passe
        if ($useSpecCar) {
            for ($i = 0; $i < $useSpecCar; ++$i) {
                $pass[] = $specials[rand(0, $specialCount - 1)];
            }
        }

        // Améliore un peu le chiffrage
        shuffle($pass);

        $pass = implode('', $pass);

        // Since some of our consonants and vowels are more than one
        // character, our string can be longer than $length, use substr()
        // to truncate the string
        return substr($pass, 0, $length);
    }
}