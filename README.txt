Instructions sommaires pour le système Envoi
============================================


Installation
------------

1) Décompresser l'archive ; laisser tout le contenu dans le même répertoire

2) Ajouter un fichier de configuration "local.conf" dans inc/conf (voir ci-dessous)

3) Ajouter dans "public" un .htaccess qui règle les variables PHP auto_prepend_file
et include_path :

php_value display_errors "stdout"
php_value auto_prepend_file "tools.php"
php_value include_path ".:/chemin/complet/vers/répertoire/inc/"

(Il est possible d'y mettre de plus une restriction d'accès)

4) Rendre le répertoire "public" accessible par le web via Apache, 
par exemple avec un alias
Il est souhaitable que les autres répertoires ne soient pas accessibles par le web


Configuration
-------------

La configuration doit être réalisée dans inc/conf/local.conf

Ce fichier règle un certain nombre de paramètres, et fournit une fonction
is_valid_user. Cette fonction réalise l'authentification. Spécification sommaire :
 - elle prend en paramètres un login, un mot de passe, et un tableau infos qu'elle doit compléter
 - elle renvoie false si le login échoue
 - sinon elle renvoie le "display name" de l'utilisateur (il sera affiché en haut de l'écran)
 - elle doit définir un certain nombre de clés dans le tableau infos
    - "nom" -> nom de famille
    - "prenom" -> prénom
    - "mail" -> adresse e-mail
    - "role" -> "PROF" pour un enseignant, "ELEV" pour un étudiant

La méthode d'authentification peut être quelconque. Exemples : via annuaire LDAP,
via BD, via fichier, ou avec en dur une liste de quelques utilisateurs dans le fichier.

Le fichier local_dummy.php fourni réalise en dur l'authentification de deux utilisateurs
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



SQLite
------

(non fonctionnel pour le moment)

Créer à la main ("touch") le fichier de base de données.
Le serveur web doit pouvoir écrire dans ce fichier.
The web server needs the write permission to not only the database file, but also the containing directory of that file.
	--> sinon cela provoque "General error: 14 unable to open database file"