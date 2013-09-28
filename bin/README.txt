Phing, PHP_CodeSniffer, PhpDOC, ... ont été installés via PEAR.
Le problème de ces paquets est qu'ils utilisent la configuration du serveur où est installé le paquet.
En conséquence, les classes et binaires ne sont ensuite plus parfaitement portables.

Le but de ce répertoire est de surcharger la config normale afin de pouvoir utiliser ces outils.

