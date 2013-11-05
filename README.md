xulub
=====

php framework using Zend Framework 1.x and Smarty


# Documentation de la version 0.8.x

## Installation

### installer xulub

Récupérer les sources dans un répertoire xulub-X.Y.Z étant la version.

#### Pour installer un projet utilisant xulub

Xulub n'est pas intégré en ressources externes aux projets, il doit être installé de manière séparé en respectant la nomenclature suivante : 
xulub-X.Y.Z avec X.Y.Z la version du framework (exemple : xulub-0.8.0). Cette nouvelle arborescence va permettre d'utiliser moins d'espace disque.

La version du framework à utiliser est indiquée dans le fichier index.php via la constante FRAMEWORK_VERSION.

### arborescence des projets

...
+-- monprojet
	+-- application
	¦   +-- Bootstrap.php      : fichier de bootstrap de l'application (voir Zend_Application)
	¦   +-- configs            : répertoire contenant la configuration de l'application
	¦   +-- controllers        : répertoire des controllers
	¦   +-- share
	¦       +-- cache          : répertoire de cache
	¦       +-- locale         : répertoire des locale
	¦       +-- logs           : répertoire des logs
	¦       +-- session        : contient les sessions
	¦       +-- smarty         : smarty (utile ?)
	+-- build.xml              : script build.xml de phing
	+-- library                : répertoire contenant les librairies partagées
	¦   +-- namespace          : exemple Authintranet, Spa, ...
	¦       +-- models         : répertoire des models
	+-- livraison.properties
	+-- public
	¦   +-- css
	¦   +-- img
	¦   +-- js
	¦   +-- index.php
	+-- scripts
	+-- sql
		+-- ddl
		+-- dml
+-- xulub-0.8.0
	+-- library                : répertoire contenant les librairies Xulub
	¦
	¦
...

## gestion du MVC

A ajouter dans le fichier application.ini pour gérer le MVC : 

...
; les contrôleurs se trouvent directement dans le répertoire controllers (donc chaine vide)
resources.frontController.modulecontrollerdirectoryname = ""
; répertoire où sont stockés les modules
resources.frontController.moduledirectory = APPLICATION_PATH "/controllers"
resources.frontController.params.displayExceptions = 0
...


## Gestion du layout

La gestion du choix du layout selon le profil de l'URL est géré par le plugin Xulub_Controller_Plugin_LayoutFromRoute. 
Les variables de configuration sont les suivantes : 

...
; layout utilisé par défaut
resources.layout.layout = public
; variable qui remplacera le contenu principal
resources.layout.contentKey = contenu
; répertoire où se trouve les layouts
resources.layout.layoutPath = APPLICATION_PATH "/controllers/layout/template"
...

A noter : le layout par défaut utilisé est défini par la variable resources.layout.layout (Si vous souhaitez utiliser par défaut le layout private, vous devez spécifier resources.layout.layout = private)

## Gestion des routes

Une route est définie dans le fichier de configuration par : 

...
resources.router.routes.global.route = ":langue/:profil/:module/:controller/*"
resources.router.routes.global.defaults.langue = "fr"
resources.router.routes.global.defaults.profil = "public"
resources.router.routes.global.reqs.profil = "public"
resources.router.routes.global.defaults.module = "Accueil"
resources.router.routes.global.defaults.controller = "Accueil"
resources.router.routes.global.defaults.action = "index"
...

Cette route indique que les URL devront être de la forme http://host/fr/public/Catalogue/Recherche, 
 * fr étant la langue
 * public le profil
 * Catalogue le module
 * Recherche le controller (avant la page, la vue)

Le nom de cette route est '''global'''.

Les valeurs par défaut sont définis par le terme defaults.

Il est ensuite possible de définir des pré-requis (requirements) sur les URL. 
Par exemple, pour n'autoriser que les profils '''public''' et '''private''', il est nécessaire de spécifier la règle suivante :

...
resources.router.routes.global.reqs.profil = "public|private"
...

Il est également possible d'utiliser des expressions régulières (à confirmer).

### gestion des domaines

Il est possible de définir des routes à partir des hostnames. A priori, quelque chose comme ça : 

...
resources.router.routes.default.type = "Zend_Controller_Router_Route_Hostname"
resources.router.routes.default.route = ":subdomain.sitename.com"
resources.router.routes.default.reqs.subdomain = "([a-zA-Z]+)"
resources.router.routes.default.defaults.subdomain = "www"

;resources.router.routes.global.type = "Zend_Controller_Router_Route_Rewrite"
resources.router.routes.default.chains.global.route = ":langue/:profil/:module/:controller/:action/*"
resources.router.routes.default.chains.global.defaults.langue = "fr"
resources.router.routes.default.chains.global.defaults.profil = "private"
resources.router.routes.default.chains.global.defaults.module = "default"
resources.router.routes.default.chains.global.defaults.controller = "index"
resources.router.routes.default.chains.global.defaults.action = "index"
...

Pour le moment, cette route ne fonctionne que si elle est définie dans le bootstrap : 

...
public function _initRoutes()
    {
        $frontController = Zend_Controller_Front::getInstance();
	$router = $frontController->getRouter();
	$router->removeDefaultRoutes();
	
	$path_route = new Zend_Controller_Router_Route(
		':langue/:profil/:module/:controller/:action/*',
		array(
			'langues' => 'fr',                    
                        'profil' => 'private',
			'module' => 'Aide',
			'controller' => 'Aide',
			'action' => 'index',
		)
	);
        $router->addRoute('global', $path_route);
	
	$dynamic_subdomain = new Zend_Controller_Router_Route_Hostname(
		':subdomain.monsite.fr',
		array(
                    	'subdomain' => 'www'
                ),
		array(
			'subdomain' => '([a-zA-Z]+)'
		)
	);
	
	$router->addRoute('subdomain_route', $dynamic_subdomain->chain($path_route));
	
	$default_route = new Zend_Controller_Router_Route_Hostname(
		'www.monsite.fr',
		array(
			'controller' => 'index',
			'action'	 => 'index'
		)
	);
	$router->addRoute('default_route', $default_route->chain($path_route));
    }
...

Dans le code PHP pour récupérer le subdomain, il faut utiliser : 

...
// dans un controller
$this->_request->getParam('subdomain');

// dans un objet 
$front = Zend_Controller_Front::getInstance();
$request = $front->getRequest();
$request->getParam('subdomain');
...

## gestion des mails

xbMail est remplacé par Xulub_Mail. Le paramètre permettant de désactiver l'envoi des mails est désactivé. Il peut avantageusement être remplacé par la gestion d'un "mail transport" en File (les mails sont stockés dans un fichier).
Le chargement de Xulub_Mail passe désormais par Xulub_Application_Resouce_Xbmail (on ne peut pas utiliser Zend_Application_Resource_Mail directement car il ne gère la possibilité de mettre un Bcc par défaut).

Pour l'utiliser, il suffit simplement d'ajouter des lignes dans le fichier de configuration. 
Plusieurs exemples : 

...
-- utilise le serveur SMTP smtp.mail.com par défaut
resources.xbmail.transport.type = "smtp"
resources.xbmail.transport.host = "smtp.mail.com"
-- définit le nom et l'email de l'expéditeur par défaut
resources.xbmail.defaultFrom.email = noreply@mail.com
resources.xbmail.defaultFrom.name = "noreply@mail.com"
-- ajoute un bcc par défaut
resources.xbmail.defaultBcc.email = "bcc@mail.com"
...


Cette configuration doit désormais être utilisée dans le cas où on ne souhaite pas que les mails soient envoyés directement.

...
-- stocke l'ensemble des mails envoyés dans le répertoire APPLICATION_PATH "/share/logs"
resources.xbmail.transport.type = "file"
resources.xbmail.transport.path = APPLICATION_PATH "/share/logs"
...

## Gestion des redirections

La classe xbHttp est dépréciée. Elle gérait jusqu'à présent les redirections ou le force download.

Pour les redirections, il est désormais possible d'utiliser le [http://framework.zend.com/manual/fr/zend.controller.actionhelpers.html#zend.controller.actionhelpers.redirector helper Redirector du Zend Framework].

Si vous souhaitez continuer l'utiliser, vous pouvez la charger manuellement dans votre fichier index.php

## Gestion des Url

Pour gérer les URL, vous pouvez utiliser 2 helpers : 
 * le helper par défaut du Zend Framework (Zend_View_Helper_View)
 * le helper de Xulub (Xulub_View_Helper_XbUrl) qui se veut un mécanisme de rétro-compatibilité avec l'existant (il gère donc les paramètres app et page)

### PHP

Depuis le contrôleur : 

...
$this->view->Url();
$this->view->XbUrl();
...

### Smarty

Créer une url dans smarty : 
 * Le helper de vue du Zend Framework
  {$this->Url(['controller' => 'Accueil', 'module' => 'Accueil'])} va générer une url sur la page d'Accueil.

Le helper de vue XbUrl devrait également fonctionner
  {$this->XbUrl(['controller' => 'Accueil', 'moduler' => 'Accueil'])} va générer une url sur la page d'Accueil.

Attention : 
 * Depuis Smarty 3, les boucles foreach ne peuvent avoir la même syntaxe pour l'élément courant que pour celui de sorti (le from doit être différent du $item). 
 * Smarty3 est plus sensible à la syntaxe.
 * Les formulaires QuickForm peuvent générés des notice de fonctions deprecated dans la ZFtoolbar. Solution => ajouter dans application.ini :

...
resources.xbview.smarty.deprecation_notices = false
...

## Paramètre de configuration de Smarty

...
; permet de désactiver l'affichage des erreurs d'appel de fonction deprecated (dans notre cas HTML_QUICKFORM)
resources.xbview.smarty.deprecation_notices = false

; Dans le cas de variable non déclarées dans template Smarty, masque les erreurs
; à utiliser avec parcimonie
resources.xbview.smarty.error_reporting = E_ALL & ~E_NOTICE

; Pour activer apc, comme moteur de cache de Smarty :
; NOTA : un fichier cacheresource.apc.php doit être présent dans un des répertoires de plugins de SMARTY
; remplace cache_handler_func
resources.xbview.smarty.caching_type = apc

; Permet de créer des sous-répertoires dans le répertoire de stockage du cache ou de templates_c
resources.xbview.smarty.use_sub_dirs = true
...

## Gestion des traductions

Le chargement de la configuration des traductions se faire via le plugin de ressource Translate.
Les paramètres de configuration sont les suivants : 

...
resources.translate.adapter = gettext
resources.translate.data = XULUB_LOCALE_DIR
resources.translate.options.scan = Zend_Translate::LOCALE_DIRECTORY
resources.translate.options.force_compile  = false
resources.translate.options.mo_compiler = "/usr/bin/msgfmt"
...

Les traductions utilisent désormais le helper de vue translate : 

...
# dans un controller : 
$this->view->translate()->translate('MA_CHAINE', array('param1'));
# dans un template 
{$this->translate('MA_CHAINE')} 
# avec le plugin smarty (deprecated) 
{t param1="value"}MA_CHAINE{/t}
... 

La conversion des chaines wiki est gérée par un helper de vue wiki dédiée. Le 2ieme paramètre est un booléen permettant d'enlever les balises P 

... 
// exemple supprimant les balises <p>
$this->view->XbWiki('ma chaine __avec__ du __wiki__', true); 

// exemple conservant les balises <p>
$this->view->XbWiki('ma chaine __avec__ du __wiki__'); 
... 
	 
ou directement dans la template

... 
{$this->XbWiki('ma chaine __avec__ du __wiki__')} 
{$this->XbWiki($ma_chaine, true)} 
{$this->XbWiki($this->translate('MA_TRADUCTION'))} 
... 
 
ou via un plugin smarty (deprecated) 

... 
{wiki}ma chaine __avec__ du __wiki__{/wiki} 
...

### processus de compilation

Le processus de compilation est exécuté lors de l'installation ou pour chaque requête si resources.xbtranslate.options.force_compile  = true.

Ce processus consiste en : 
 * concaténation de l'ensemble des fichiers languages.xml dans un fichier global-languages.xml stocké dans share/locale
 * conversion du fichier global-languages.xml en N fichier po (fichier texte) par langue dans share/locale/<language>/LC_MESSAGES/messages.po
 * conversion des fichiers messages.po en fichier .mo (format binaire gettext) par langue dans share/lcoale/<language>/LC_MESSAGES/messages.mo

Le Zend_Translate s'occupe de lire le fichier messages.mo

### format du fichier languages.xml

...
<?xml version="1.0" encoding="ISO-8859-15" ?>
<translations>
    </sentence>
	<sentence key="L_APPLIFORMS_AF_PAS_APPLI">
    	<translation language="fr_FR" value="Accès application Forms : problème dans le récupération de l'application pour ce contexte" />
    </sentence>
    ...
    <sentence key="L_APPLIFORMS_AF_PB_CODE_STRUCTURE">
    	<translation language="fr_FR" value="Accès application Forms : Ce contexte n'est pas un contexte autorisé." />
    </sentence>
</translations>
...

N.B : 
 * A noter qu'on utilise un encodage ISO-8859-15 car la BDD est toujours dans ce format.

## gestion des formulaires

Remplacer les 

...
addFormRule(array('MaClasse', 'maFonction'));
...

par

...
addFormRule(array('MaClasseController', 'maFonction'));
...


## Gestion des ACL

La gestion des ACL est désormais assurée par un plugin du front controller (Xulub_Controller_Plugin_CheckAcl). Dès que vous le chargez, ce plugin va s'occuper de gérer tout seul la gestion des ACL.

Pour le charger : 

...
$options = $this->getOptions();
$formatressourcename = Xulub_Controller_Plugin_CheckAcl::XULUB_ACL_FORMAT_PAGE;
if (isset($options['xulub']['acl']['formatressourcename']))
{
   $formatressourcename = $options['xulub']['acl']['formatressourcename'];
}
Zend_Controller_Front::getInstance()->registerPlugin(
                new Xulub_Controller_Plugin_CheckAcl(
                        $user,
                        $formatressourcename)
                );
...

idéalement à placer dans le Boostrap.

 * Si l'utilisateur est authentifié et qu'il ne dispose pas des droits, il est redirigé vers module : default, controller : error et action : noAccess.
 * Si l'utilisateur n'est pas authentifié, il est redirigé vers la page d'accueil.

## Gestion de GoogleAnalytics

Pour ajouter le code GA dans les pages, il est possible d'utiliser le helper de vue Xulub_View_Helper_XbGoogleAnalytics.
Usage : 

...
// dans le controller
$options = array('param' => 'value');
$this->view->XbGoogleAnalytics('id tracker', $options);

// Possibilité d'ajouter des options
$this->view->XbGoogleAnalytics()->addTrackerOption('trackPageview', 'ma valeur');
$this->XbGoogleAnalytics();
...

Pour initialiser GoogleAnalytics sur l'ensemble des pages, il est possible d'utiliser une méthode de Bootstrap.
Exemple : 

...
    /**
     * Initialisation de GoogleAnalytics
     */
    public function _initStats()
    {
        // On récupère les options dans la section stats
        $options = $this->getOption('stats');
        if ( isset($options['id_ga']) && is_string($options['id_ga']))
        {
            $trackerId = $options['id_ga'];
            unset($options['id_gda']);
        }

        // On récupère les options si besoin
        if (isset($options['options']) && is_array($options['options']) )
        {
            $options = $options['options'];
        }

        // Si on dispose d'un trackerId, on initialise GoogleAnalytics
        if (!empty($trackerId))
        {
            $this->layout->getView()->xbGoogleAnalytics($trackerId, $options);
        }
    }
...

avec dans le fichier de configuration : 

...
stats.id_ga = UA-123456-01
stats.options.setDomainName = .mydomain.com
...

## gestion des helpers

### Appel des Helpers

Il existe 2 types de helpers : 
 * helper de vue (View_Helper)
 * helper d'action (Action_Helper)

Par convention, l'ensemble des helpers spécifiques à Xulub se nommer XbMonSuperNom (exemple : XbGooogleAnalytics).

L'appel des helpers diffèrent selon leur type : 

#### helper de vue

Les helpers de vue sont stockées dans : 
 * library/internal/Xulub/View/Helper pour les helpers spécifiques à Xulub
 * library/vendor/Zend/View/Helper pour les helpers spécifiques à Zend

Pour appeler les helpers de vue dans un controlleur : 

...
$this->view->XbMonSuperHelper();
...

Depuis Smarty : 

...
$this->XbMonSuperHelper();
...

Pour passer, des paramètres : 

...
$this->XbUrl(['controller' => 'mon controller', 'module' => 'mon module', 'action' => 'mon action' ]
...

#### helper d'action

Les helpers de vue sont stockées dans : 
 * library/internal/Xulub/Controller/Action/Helper pour les helpers spécifiques à Xulub
 * library/vendor/Zend/Controller/Action/Helper pour les helpers spécifiques à Zend

Pour appeler les helpers de vue dans un controlleur : 

...
$this->_helper->XbMonSuperHelper();
// ou
$this->_helper->getHelper('XbMonSuperHelper');
...

### créer un helper de vue

Les helpes de vue doivent être positionnés dans Xulub/View/Helper ou NameSpace/View/Helper (exemple : EspacePro/View/Helper).

Il est nécessaire d'indiquer au framework les répertoires où il peut trouver les helpers. Le chargement se fait dans Xulub_Application_Resource_XbView.
Pour ajouter un répertoire, il faut indiquer dans le fichier application.ini : 

...
resources.xbview.helperDirs.Namespace_View_Helper_ = "Namespace/View/Helper"
...

Exemple : 

...
resources.xbview.helperDirs.EspacePro_View_Helper_ = "EspacePro/View/Helper"
...

(ne pas oublier le underscore (_) )


Par défaut, les répertoires de Helper sont : 

...
 'Xulub/View/Helper' => 'Xulub_View_Helper_',
 'Zend/View/Helper' => 'Zend_View_Helper_'
...


## Classes ou fonctions non supportées

 * xbDate (à remplacer par Zend_Date)

## Génération login / mot de passe

 * library/internal/Xulub/Utils/String.php : fonction generateRandomString permettant de générer une chaine de caractère. 
 * library/EspacePro/Auth/GenerateRandomString.php : classe permettant de générer login / mot de passe.

## Récupération du controller

Pour récupérer le nom du controller courant :

...
$request = Zend_Controller_Front::getInstance()->getRequest();
$page = $request->getControllerName();
...

et non pas : 

...
$page = $this->_request->getControllerName();
...

## Gestion des User

Pour gérer les utilisateurs, il est possible d'ajouter un Namespace_Application_Resource_User qui s'occupe d'initialiser le user.

La récupération du user est ensuite dans un controlleur possible via un mécanisme de ce type: 

...
    /**
     * Renvoie l'objet Xulub_User
     * 
     * @return Xulub_User
     */
    public function getUser()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('xbuser')) {
            return false;
        }
        $user = $bootstrap->getResource('xbuser');
        return $user;
    }
...

## Amélioration des performances

Pour améliorer les performances, le Zend Framework a publié un certain nombre de recommandations (http://framework.zend.com/manual/fr/performance.classloading.html).

### Amélioration des performances du chargement des plugins

Il est possible d'améliorer le chargement des plugins ZF (cf. http://framework.zend.com/manual/fr/zend.loader.pluginloader.html#zend.loader.pluginloader.performance.example). 

Pour l'activer, il est nécessaire d'ajouter dans le fichier de configuration : 

...
resources.xbconfig.enablePluginLoaderCache = 1
...

Ce mécanisme est ensuite traité dans Xulub_Application_Resource_Xbconfig.

### Amélioration des performances de Zend_Application

Par défaut, Zend_Application charge la configuration du fichier application.ini via Zend_Config_Ini. Ce mécanisme est assez lourd en terme de performance. Il est possible d'améliorer les performances de ce mécanisme en mettant en cache le chargement de Zend_Config.
C'est le rôle de Xulub_Application qui va stocker par défaut la configuration du fichier application.ini en cache (dans le répertoire application/share/cache). Il est également possible de passer plusieurs fichiers application.ini à Zend_Application ou Xulub_Application.

...
// dans le fichier index.php
// -- syntaxe par défaut
$application = new Zend_Application(
                    APPLICATION_ENV,
                    APPLICATION_PATH . '/configs/application.ini'
);

// -- syntaxe utilisant Xulub_Application permettant la mise en cache de application.ini
$application = new Xulub_Application(
                    APPLICATION_ENV,
                    APPLICATION_PATH . '/configs/application.ini'

// -- syntaxe utilisant Xulub_Application avec plusieurs fichiers application.ini
$application = new Xulub_Application(
                    APPLICATION_ENV,
                    array('config' => array(
                                         APPLICATION_PATH . '/configs/application.ini',
                                        'mon-autre-fichier-application.ini'
                                      )
                    ));
...

### Suppression des require_once

cf. http://framework.zend.com/manual/fr/performance.classloading.html

Voir pour ajouter une tâche phing.

### Mise en cache des objets Zend

Il y a plein d'objets Zend qui peuvent être mis en cache : 

...
$cache = ...un objet de type Zend_Cache...;

// Cache de Zend_Date
Zend_Date::setOptions(array('cache' => $cache));

// Cache de Zend_Feed_Reader
Zend_Feed_Reader::setCache($cache);
...

### Amélioriation des performance de Zend_Feed

...
// on met un timeout sur les connexions HTTP à 1 seconde
// Permet de réduire la latence de la connexion si le service n'est pas joignable
$configHttpClient = array(
  'timeout'      => 1,
  );
$httpClient = new Zend_Http_Client(null, $configHttpClient);
Zend_Feed::setHttpClient($httpClient);
...

### Amélioration des performances HTTP

Une des bonnes pratiques de performance Web est de réduire le nombre de requêtes HTTP. Une des solutions est de concaténer l'ensemble des fichiers JS et CSS en un fichier unique.

Xulub propose un mécanisme de concaténation/minification. 

Pour l'activer, il est nécessaire d'utiliser les aides de vue suivante : 
 * Xulub_Helper_View_XbHeadLink (en remplacement de Zend_Helper_View_HeadLink)
 * Xulub_Helper_View_XbHeadScript (en remplacement de Zend_Helper_View_HeadScript)

Concrètement, il suffit de remplacer : 
 * $this->headLink par $this->!XbHeadLink
 * $this->headScript par $this->!XbHeadScript

Par défaut, la concaténation et la minification est activée. Si vous souhaitez la désactiver, vous pouvez utiliser la configuration suivante : 

...
; minification des fichiers css
resources.xbview.css.minify = true
; concaténation des fichiers css
resources.xbview.css.concatenate = true
; minification des fichiers js
resources.xbview.js.minify = true
; concaténation des fichiers js
resources.xbview.js.concatenate = true
...

A noter que si la variable version a été définir dans le fichier application.ini, elle sera automatiquement ajoutée aux fichiers CSS et JS : 

...
; dans application.ini
version = 1.5.0

; cela générera : 
<link href="/auth/css/global.css?version=1.5.0" media="all" rel="stylesheet" type="text/css" />

ou
<script type="text/javascript" src="/auth/js/jquery-1.4.2.js?version=1.5.0"></script>
...

## Envoi de fichier au navigateur

Les méthodes xbHttp::headerForceDownload et xbHttp::headerForceDownloadByFile sont remplacées par une aide d'action $this->_helper->Headers->headerForceDownloadByFile()

### suppression des config.xml

A exécuter pour nettoyer rapidement une fois le reprise effectuée

...
find ./application/controllers/ -name config.xml -exec svn del '{}' \;
...


## Passage et Récupération de paramètres

Les anciennes méthodes pour passer des variables entre les controllers ( ->getArgs() ) ou récupérer des paramètres de l'url sont ( fonction parametreUrl() ) ne sont plus supportés.

Il faut les remplacer par $this->_getParam() dans un controller ou $this->request->getParam() pour des données dans l'url.

Vérifier l'utilisation de getArgs

...
find . -name *.php -exec grep -l "getArgs" '{}' \;
...

Remplacer toutes ces méthodes

...
find . -name *.php -exec /opt/csw/bin/gsed  's/getArgs/_getParam/g' -i '{}' \; 2>>/dev/null
...

## Utilisation de placeholder

Zend Framework permet de créer des placeholder qui correspond au mécanisme des addComposant jadis utilisés.

dans le contrôleur (autrefois le addComposant avec le hook) :

...
$this->view->placeholder('contenu_onglet')->set($this->view->action('index', $controller, $module, $params));
...

dans la template, il suffit de rappeler le placeholder (ici contenu_onglet) :

...
{$this->placeholder('contenu_onglet')} 
...

## Gestion de cache

Xulub utilise, par défaut, Zend_Cache_Manager. Grâce à Zend_Cache_Manager, il est possible d'activer des caches par type (template, database, ...).

...à compléter (mode de fonctionnement de l'utilisation du cache) ...

Il est possible de logger les éléments stockés en cache via l'ajout d'une directive dans le fichier application.ini : 

...
resources.xbcachemanager.database.frontend.options.logging = true
...

Lorsque logging est à true, le fichier de log est alimenté par les traces d'utilisation des composants Zend_Cache.

Le mécanisme de chargement du logger est effectué dans Xulub_Application_Resource_xbCacheManager.


## Mise à jour de Pear

Pear est intégrée dans subversion. Il est nécessaire, parfois, de le mettre à jour.
La procédure est la suivante :

1- On modifie la configuration de pear : 

...
$ pear config-set bin_dir /chemin/vers/xulub/library/vendor/pear.20100302/bin
$ pear config-set doc_dir /chemin/vers/xulub/library/vendor/pear.20100302/doc
$ pear config-set php_dir /chemin/vers/xulub/library/vendor/pear.20100302/lib/php
$ pear config-set data_dir /chemin/vers/xulub/library/vendor/pear.20100302/data
$ pear config-set cfg_dir /chemin/vers/xulub/library/vendor/pear.20100302/cfg
$ pear config-set data_dir /chemin/vers/xulub/library/vendor/pear.20100302/data
$ pear config-set test_dir /chemin/vers/xulub/library/vendor/pear.20100302/tests
...
