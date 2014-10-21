# Envoi, un système de livraison de travaux étudiants

envoi /ɑ̃.vwa/ : dédicace, soit en vers, soit en prose.

envoi /ˈɑnvɔɪ/: a short stanza at the end of a poem, used either to address a person or to comment on the preceding body of the poem.

_Envoi_ est un système qui permet à des professeurs de demander à leurs étudiants de _livrer_ leurs travaux en ligne. Le professeur crée une _spécification de livraison_ qui spécifie combien de fichiers sont attendus pour un travail, de quels types, etc. Un code est associé à cette spécification : les étudiants n'ont plus qu'à donner ce code pour livrer leur travail. Le type des fichiers est vérifié via un mécanisme de filtres extensible. Le système est multi-professeurs. Il nécessite un serveur web avec PHP et MySQL.

_Envoi_ is a system that allows professors to ask their students to turn in assignments on-line. Currently it is available in French only.


## Installation

1. Décompresser l'archive ; laisser tout le contenu dans le même répertoire

2. Ajouter un fichier de configuration `local.conf` dans `inc/conf` (voir ci-dessous)

3. Depuis le répertoire du projet :
   ```
   cd install
   php -d include_path=.:../inc install.php
   ```

4. Ajouter dans `public` un `.htaccess` qui règle les variables PHP `auto_prepend_file`
et `include_path` :
   
   ```
   php_value display_errors "stdout"
   php_value auto_prepend_file "tools.php"
   php_value include_path ".:/chemin/complet/vers/répertoire/inc/"
   ```
   
   (Il est possible d'y mettre de plus une restriction d'accès)

5. Rendre le répertoire "public" accessible par le web via Apache, 
par exemple avec un alias. Il est souhaitable que les autres répertoires ne soient pas accessibles par le web

6. Créer un répertoire "data" accessible par Apache en lecture et écriture



## Configuration

La configuration doit être réalisée dans `inc/conf/local.conf`.

Bien régler les paramètres d'accès à la base de données et les chemins.

Ce fichier règle un certain nombre de paramètres, et fournit une fonction
`is_valid_user`. Cette fonction réalise l'authentification. Spécification sommaire :
 - elle prend en paramètres un login, un mot de passe, et un tableau infos qu'elle doit compléter
 - elle renvoie false si le login échoue
 - sinon elle renvoie le _display name_ de l'utilisateur (il sera affiché en haut de l'écran)
 - elle doit définir un certain nombre de clés dans le tableau infos
    - `nom` -> nom de famille
    - `prenom` -> prénom
    - `mail` -> adresse e-mail
    - `role` -> `PROF` pour un enseignant, `ELEV` pour un étudiant

La méthode d'authentification peut être quelconque. Exemples : via annuaire LDAP,
via BD, via fichier, ou avec en dur une liste de quelques utilisateurs dans le fichier.

Le fichier `local_dummy.php` fourni réalise en dur l'authentification de deux utilisateurs
foo et bar.

Voici les grandes lignes d'une authentification LDAP :

    static function is_valid_user($login, $passwd, &$infos) {
        $ds=ldap_connect("ldaps://ldap.example.com");

        if($ds) {
            $dn = "uid=" . ldap_escape($login, TRUE) . ",ou=...,dc=....";
            $r=@ldap_bind($ds, $dn, $passwd);

            if($r) {
                $sr = ldap_search($ds, $dn, "objectClass=*");
                $attrs = ldap_get_attributes($ds, ldap_first_entry($ds, $sr));

                $infos["nom"] = $attrs[...][0];
                $infos["prenom"] = $attrs[...][0];
                $infos["mail"] = $attrs[...][0];

                $infos["role"] = ............. ? "PROF" : "ELEV";

                return ...; // display name
            } else {
                echo "<!-- LDAP ERROR: " . ldap_error($ds) . " -->";
                return false;
            }
        } else {
            echo "<!-- LDAP: Cannot get connection! -->";
        }
    }


La fonction `is_valid_email` indique si un e-mail est valide. Par exemple, elle
peut vérifier qu'il est bien présent dans un annuaire LDAP. La forme la plus
naïve de cette fonction est :

    static function is_valid_email($login, $passwd, $email, &$infos) {
        return TRUE;
    }



## Utilisation

1 livraison (ou "rendu"), ou plus précisément "une *spécification* de rendu" est créé par un enseignant :
 - correspond à une livraison attendue de chaque groupe d'étudiants
 - peut posséder plusieurs fichiers (ou plus précisément *spécifications* de fichiers)
 - chaque spéc. de fichier peut être vérifiée par un script Unix ("filtre")
 - possède un CODE unique (avec code détecteur d'erreur), qui est à fournir au groupe d'étudiants concerné
 - peut être créée par CLONAGE d'une autre spéc. de livraison (pratique pour demander une livraison
identique d'année en année, ou bien à plusieurs groupes d'étudiants)

1 étudiant rend une livraison, ou plus précisément "une *instance* de livraison", conforme à une spécification
de livraison donée par un prof
 - en utilisant le code
 - en fournissant un fichier (instance de fichier) pour chaque spéc. de fichier indiquée par le prof
 - les fichiers non conformes sont rejetés
 - la livraison n'est acceptée que lorsque les fichiers sont conformes
 - un e-mail est alors envoyé


# SQLite

(non fonctionnel pour le moment)

Créer à la main (`touch`) le fichier de base de données.
Le serveur web doit pouvoir écrire dans ce fichier. (The web server needs the write permission to not only the database file, but also the containing directory of that file.)
Sinon cela provoque "General error: 14 unable to open database file"



# Tests

```
php -d include_path=.:../inc tests
```


# Maintenance

## Passer au stockage des fichiers sur disque (SVN r10 -> r11)

Utile uniquement si vous utilisiez Envoi avant début 2014.

1. Mettre en marche le système WIP (Work In Progress) dans `local.php`

2. Faire un backup
   
   ```
   mysqldump --max_allowed_packet=512M -p --result-file=livraison.sql livraison
   ```

3. Mettre à jour le logiciel

4. Créer un répertoire data

5. Dans install :
   ```
   php -d include_path=.:../inc extractfiles.php
   ```

6. Faire un `chgrp -R` : mettre le groupe apache et un `chmod -R` : mettre g+w

7. Dans la table `fichierDonne`, supprimer la colonne `contenu` de type longblob
(par exemple avec phpMyAdmin)

8. Optimiser la base :
   
   ```
   mysqlcheck -o livraison
   ```

9. Retirer le WIP


## Licences des médias

- Icône gamepad : http://commons.wikimedia.org/wiki/File:Gnome-input-gaming.svg (GPL)
- Icône URL : http://commons.wikimedia.org/wiki/File:Gnome-web-browser.svg (GPL)
- Icône document : http://commons.wikimedia.org/wiki/File:Gnome-x-office-document.svg (GPL)
- Icône e-mail : http://commons.wikimedia.org/wiki/File:Gnome-mail-unread.svg (GPL)
- Icône plus : http://commons.wikimedia.org/wiki/File:Symbol_support_vote.svg (domaine public)
- Icône edition : http://commons.wikimedia.org/wiki/File:Octicons-tools.svg (MIT)
- Icône corbeille : http://commons.wikimedia.org/wiki/File:Octicons-trashcan.svg (MIT)

## Licence

Envoi is licensed under the terms of the GNU GPLv3

© Christophe Jacquet, 2009-2014.