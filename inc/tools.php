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

function baseURL() {
	$s = empty($_SERVER["HTTPS"]) ? ''
		: ($_SERVER["HTTPS"] == "on") ? "s"
		: "";
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
		: (":".$_SERVER["SERVER_PORT"]);
	return implode("/", explode("/", $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'], -1));
}

function strleft($s1, $s2) {
	return substr($s1, 0, strpos($s1, $s2));
}

function head($titre, $role = false, $mustBeLoggedIn = true, $display = true) {
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
        //| <a href='listeseances.php'>Séances</a></div>";

        
    } else if($mustBeLoggedIn) die("<a href=\"index.php\">Veuillez vous connecter</a>.");

    if($role && $_SESSION["role"] != $role) die("Page réservée.");

    $metatitre = $titre ? "Livraison de travaux – $titre" : "Livraison de travaux";
    $h1titre = $titre ? $titre : "Livraison de travaux";

    if($display) {
    echo <<<FIN
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>$metatitre</title>
        <link rel="stylesheet" type="text/css" href="rendu.css" />
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
        <meta name="format-detection" content="telephone=no" />
    </head>
    <body>
    $loggedin

    <h1>$h1titre</h1>
FIN;
    }
}

function foot() {
    echo <<<FIN
    </body>
</html>
FIN;
}


function fileIdToPath($id, &$path, &$filename) {
    $path = "";
    $limit = 256;
    
    $filename = sprintf("%x", $id);
    // ensure the filename length is even: it allows for better display
    // of the file listing
    if(strlen($filename) % 2 == 1) {
        $filename = "0" . $filename;
    }
    
    while($id >= $limit) {
        $path .= sprintf("x%02x/", $id % $limit);
        $id /= $limit;
    }
    
    $path = Local::$basedir . "/data/" . $path;
}


function createFilePath($path) {
    if(is_dir($path) === FALSE) {
        mkdir($path, 0777, true);
    }
    
    return $path;
}

#function scriptdir() {
#    $script_directory = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/'));
#    return $script_directory . "/scripts/";
#}


?>