<?php
class Local {
    static $db_dsn = "mysql:dbname=MyDatabaseName;host=localhost";
    static $db_user = "username";
    static $db_password = "password";

    static $smtp_relay = "smtp.example.com";
    static $from_email = 'noreply@example.com';
    static $from_name = 'Livraison';

    static $basedir = "/absolute/path/to/the/base/directory";

    static $wip = FALSE;                # true if you want to temporarily block access to the server while some work is in progress
    static $wip_authorized = array();   # list of IPs that can still access the server while there is work in progress

    static $locale = "fr_FR";
    static $email_placeholder = "Prenom.Nom@example.com";    
    static $timezone = "Europe/Paris";


    static function is_valid_user($login, $passwd, &$infos) {
        if($login == "foo" && $passwd == "foo") {
            $infos["nom"] = "Foo";
            $infos["prenom"] = "Henri";
            $infos["role"] = "ELEV";
            $infos["mail"] = "foo@example.com";
            return "Henri Foo";
        } elseif($login == "bar" && $passwd == "bar") {
            $infos["nom"] = "Bar";
            $infos["prenom"] = "Christophe";
            $infos["role"] = "PROF";
            $infos["mail"] = "bar@example.com";
            return "Christophe Bar";
        }

        return false;
    }
    
    static function is_valid_email($login, $passwd, $email, &$infos) {
        return TRUE;
    }

}

?>