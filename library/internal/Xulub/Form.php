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
 * @package    Xulub_Form
 *
 * @desc
 *
 * @author DSI CNFPT
 * @license GPL
 * @version $Id$
 */
//require 'HTML/QuickForm.php';
Zend_Loader::loadClass("HTML_QuickForm");

// Chargement des classes PEAR créés pour le catalogue

Zend_Loader::loadFile(
    'advcheckboxgroup.php',
    FRAMEWORK_PATH . '/library/vendor/Quickform'
);

Zend_Loader::loadFile(
    'selectarray.php',
    FRAMEWORK_PATH . '/library/vendor/Quickform'
);

Zend_Loader::loadFile(
    'advmultiselect.php',
    FRAMEWORK_PATH . '/library/vendor/Quickform'
);

class Xulub_Form extends HTML_QuickForm
{
    /**
	 * Booléen permettant d'indiquer si l'utilisateur est autorisé
	 * à valider le formulaire s'il n'accepte pas les cookies
	 *
	 * Par défaut, validation sans restriction
	 *
	 * @var boolean
	 */
    private $_enableCookieRequired = false;


    /**
     * Flag permettant d'indiquer si on active ou non la validation des Tokens.
     * permet de contrôler qu'il n'y a pas d'attaque CSRF
     *
     * @var bool
     */
    private $_enableValidateToken = true;

    /**
     * Booléen permettant de consulter et vérifier les ACL avant d'accepter
     * le POST d'un formulaire. Les ACL se baseront sur une ressource possédant
     * le nom du hook et le privilege 'poster'
     *
     * @var boolean
     */
    private $_checkingCredential = false;

    /**
     * Nom de la ressource à vérifier dans les ACL au moment de la validation
     * du formulaire. Utilisé uniquement si _checking_credential est à true
     *
     * @var string
     */
    private $_ressourceAVerifier = null;

    public function __construct($nameForm, $method, $action)
    {
        // Le dernier paramètre true / false permet d'identifier de manière
        // unique le formulaire nécessiter d'ajouter les champs hidden dans
        // le formulaire.
        parent::HTML_QuickForm($nameForm, $method, $action, '', null, true);

        // on supprime l'attribut name de la balise form
        // pour des raisons de validation de code HTML
        $this->removeAttribute('name');

        // on crée un champ token qui contiendra un identifiant unique
        // cet identifiant unique sera comparé à la valeur en session
        // lors de la soumission, si les deux valeurs ne sont pas identiques :
        //  * un utilisateur essaye de soumettre le formulaire depuis une page
        //  externe
//        $form_token = md5(uniqid(microtime(), 1));
//        if ( xbSession::isStarted() )
//        {
//
//            $tab_form_token = array();
//            $session_tab_form_token = xbSession::get('tab_form_token');
//            if ( is_array($session_tab_form_token) )
//            {
//                // on conserve 10 token dans le tableau des token
//                if ( count($session_tab_form_token) > 10 )
//                {
//                    $session_tab_form_token = array_splice($session_tab_form_token, -10);
//                }
//                $tab_form_token = array_merge($tab_form_token, $session_tab_form_token);
//            }
//
//            $tab_form_token[] = $form_token;
//            // on stocke en session l'ensemble des tokens
//            // des identifiants uniques
//            // uniquement si le formulaire n'a pas été resoumis
//            xbSession::set('tab_form_token', $tab_form_token);
//        }
//
//        // on ajoute un champ caché qui va contenir le token
//        $this->addElement('hidden', 'form_token', $form_token);
//        // obligé de le faire sinon la valeur du token n'est pas modifié
//        $this->getElement('form_token')->setValue($form_token);
//
//        // on ajoute une règle de validation
//        // pour vérifier les tokens
//        $this->addFormRule(array(&$this, 'validateForm'));
    }

    /**
     * Permet de bloquer la validation du formulaire
     * si l'utilisateur refuse les cookies
     *
     */
    public function enableCookieRequired()
    {
        $this->_enableCookieRequired = true;
    }

    /**
     * Permet à l'utilisateur de valider un formulaire
     * même s'il refuse les cookies
     *
     */
    public function disableCookieRequired()
    {
        $this->_enableCookieRequired = false;
    }

    /**
     * Détermine si le formulaire est bloqué
     * si l'utilisateur n'accepte pas les cookies
     *
     * @return boolean
     */
    public function isCookieRequired()
    {
        return $this->_enableCookieRequired;
    }

    /**
     * Active la validation des tokens lors de la soumission d'un formulaire
     *
     */
    public function enableValidateToken()
    {
        $this->_enableValidateToken = true;
    }

    /**
     * Désactive la validation des tokens lors de la soumission d'un formulaire
     *
     */
    public function disableValidateToken()
    {
        $this->_enableValidateToken = false;
    }

    /**
     * Déterminer l'état de la validation des tokens lors de la soumission
     * d'un formulaire
     *
     * @return bool
     */
    public function isEnabledValidateToken()
    {
        return $this->_enableValidateToken;
    }

    /**
     * Permet de bloquer la validation du formulaire
     * si l'utilisateur n'a pas les priviléges ACL 'poster'
     * pour ce hook/page
     *
     */
    public function enableCheckingCredential($ressource)
    {
        $this->_checkingCredential = true;
        $this->_ressourceAVerifier = $ressource;
    }

    /**
     * Détermine si on doit vérifier les ACL de l'utilisateur pour ce formulaire
     * Si aucune la ressrouce à vérifier n'a pas été définie au préalable,
     * on lance une exception.
     *
     * @return boolean
     */
    public function hasCredential()
    {
        if ($this->_checkingCredential && is_null($this->_ressourceAVerifier)) {
//            throw new xbException(
//            'FORM_UNDEFINED_RESSOURCE',
//            xbException::NIV_EXCEPTION_APPLICATIVE,
//            'La ressource à vérifier pour ce formulaire n\'a pas été définie.'
//            );
        }

        return $this->_checkingCredential;
    }

    /**
     * Retourne le nom de la ressource a vérifier, si les ACL sont activées
     * pour ce formulaire, sinon retourne false
     *
     * @return false|string
     *
     */
    public function getRessource2Check()
    {
        if (is_null($this->_ressourceAVerifier)) {
            return false;
        }

        return $this->_ressourceAVerifier;
    }

    /**
     * fonction globale de validation du formulaire
     * Appelle les autres fonctions de validation des formulaires
     * Renvoie un tableau si des erreurs ont été trouvées
     * TRUE si tout est ok.
     *
     * @return array|true
     */
    public function validateForm()
    {
        // on souhaite bloquer l'appel de ce formulaire si :
        //  *  l'utilisateur n'accepte pas les cookies
        if ($this->isCookieRequired() === true) {

            $result = $this->_validateFormCookie();

            if ( $result !== true) {
                return $result;
            }
        }

        if ($this->_enableValidateToken === true) {
            return $this->_validateFormToken();
        }

        return true;
    }

    /**
     * Fonction qui vérifie que l'utilisateur accepte
     * les cookies pour valider ce formulaire.
     *
     * si l'utilisateur accepte les cookies, renvoie true
     * Si l'utilisateur n'accepte pas les cookies, on renvoie une erreur
     * Quickform
     *
     * @return array|true
     */
//    private function _validateFormCookie()
//    {
//        if ( xbSession::acceptCookie() )
//        {
//            return true;
//        }
//        // l'utilisateur refuse les cookies
//        else
//        {
//            $premier_element_qf = $this->_getFirstElementName();
//            $erreurs[$premier_element_qf] = translate('L_FORM_REFUS_COOKIE');
//        }
//        return $erreurs;
//    }


    /**
     * fonction qui sera utilisée par QuickForm
     * pour valider les tokens
     * Renvoie true si tout est ok
     * Renvoie un tableau avec le premier élement de quickform
     * si problème.
     *
     * @return array|true
     */
    private function _validateFormToken()
    {
        $erreurs = array();
        $tabFormTokenSession = xbSession::get('tab_form_token');
        $formTokenPost = $this->exportValue('form_token');

        debug_display($tabFormTokenSession);
        debug_display($formTokenPost);

        // si la session n'est pas démarrée
        // on ne peut pas faire ce test
        // tout est ok
        if ( xbSession::isStarted() === false ) {
            return true;
        } elseif (in_array($formTokenPost, $tabFormTokenSession)) {
            return true;
        } else {
            // sinon, ça ressemble à une attaque CSRF
            // récupère le premier élémenet du formulaire
            $premierElementQf = $this->_getFirstElementName();
            $erreurs[$premierElementQf] = translate('L_FORM_TENTATIVE_CSRF');
            //Zend_Registry::get('log')->notice('tentative CSRF');
        }

        return $erreurs;
    }

    /**
     * Bloque le formulaire en désactivant les champs, supprimant les boutons
     * et ajoute un message pour prévenir l'utilisateur.
     *
     */
    public function blockUserAction()
    {
        $this->setElementError(
            $this->_getFirstElementName(),
            translate('L_FORM_ACL_REFUSE')
        );
        $this->freeze();
        $this->_removeButtons();
        return true;
    }

    /**
     * Retire tout les boutons du formulaire. Se base sur le type de l'élément
     * quickform : reset, submit ou button.
     *
     * @return true
     */
    private function _removeButtons()
    {
        foreach ($this->_elements as $element) {
            if (($element->getType() == 'submit')
                || ($element->getType() == 'button')
                || ($element->getType() == 'reset')
            ) {
                $this->removeElement($element->getName());
            }
        }
        return true;
    }

    /**
     * récupère le nom du premier élémenet du formulaire
     * qui n'est pas un champs caché
     * c'est pas propre mais pas d'autres solutions
     * l'erreur quickform doit obligatoirement être associé
     * à un champ de formulaire existant
     *
     * Ce mécanisme est utilisé pour associer une erreur quickform
     * à un champs de formulaire (cf. validateFormToken, ...)
     *
     * Renvoie une exception si ne trouve pas le premier élément du formulaire
     *
     * @return string
     */
    private function _getFirstElementName()
    {
        foreach ($this->_elements as $element) {
            if ($element->getType() != 'hidden') {
                $premierElementQf = $element->getName();
                return $premierElementQf;
            }
        }
        if (empty($premierElementQf)) {
            throw new xbException(
                'XBFORM_VALIDATE_TOKEN',
                xbException::NIV_EXCEPTION_APPLICATIVE,
                'Impossible d\'associer l\'erreur à un champ de formulaire'
            );
        }
    }

    /**
	 * Nettoye la chaine en enlevant les balises html
	 *
	 * @param string $chaine
	 * @return $chaine
	 */
    public static function postSubmitFilter($chaine)
    {
        $chaine = strip_tags($chaine);
        return $chaine;
    }

    /**
     * Transforme les entitées html
     *
     * @param string $chaine
     * @return $chaine
     */
    public static function preDisplayFilter($chaine)
    {
        $chaine = htmlentities($chaine);
        return $chaine;
    }
}