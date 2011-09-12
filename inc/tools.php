<?php


/*
    This file is part of Envoi.

    Envoi is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Envoi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Envoi.  If not, see <http://www.gnu.org/licenses/>.

    (c) Christophe Jacquet, 2009-2011.
 */


require_once("conf/local.php");
require("db.php");

DB::connect();

function head($titre, $role = false, $mustBeLoggedIn = true) {
    // work in progress?
    if(Local::$wip && ! in_array($_SERVER["REMOTE_ADDR"], Local::$wip_authorized)) {
        echo "<div class=\"error\">Travaux en cours. Merci de patienter quelques minutes, ou de contacter le mainteneur...</div>";
        exit;
    }

    // start session only if needed
    if(session_id() == "") session_start();

    $loggedin = "";
    if(isset($_SESSION["login"])) {
        $loggedin = "<div id=\"loggedBox\">Connecté en tant que " . $_SESSION["displayName"] .
                " (" . (($_SESSION["role"] == "PROF") ? "enseignant" : "étudiant") . ")" .
                ". <a href=\"logout.php\">Déconnexion</a>.</div>";

        if($_SESSION["role"] == "PROF") $loggedin .= "<div id=\"menuBox\"><a href=\"index.php\">Accueil</a> | <a href=\"listerendus.php\">Liste livraisons</a> | <a href=\"ajoutrendu.php\">Ajout livraison</a></div>";

        
    } else if($mustBeLoggedIn) die("<a href=\"index.php\">Veuillez vous connecter</a>.");

    if($role && $_SESSION["role"] != $role) die("Page réservée.");

    $metatitre = $titre ? "Livraison de travaux – $titre" : "Livraison de travaux";
    $h1titre = $titre ? $titre : "Livraison de travaux";

    echo <<<FIN
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>$metatitre</title>
        <link rel="stylesheet" type="text/css" href="rendu.css" />
    </head>
    <body>
    $loggedin

    <h1>$h1titre</h1>
FIN;
}

function foot() {
    echo <<<FIN
    </body>
</html>
FIN;
}

#function scriptdir() {
#    $script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
#    return $script_directory . "/scripts/";
#}


?>
