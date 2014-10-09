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


require("code.php");

session_start();

$loginMessage = "";

if(isset($_POST["login"]) && isset($_POST["password"])) {
    // check login/password

    $login = $_POST["login"];
    $passwd = $_POST["password"];

    $infos = array();
    if($displayName = Local::is_valid_user($login, $passwd, $infos)) {
        $loginMessage = "<p>Connexion réussie pour l'utilisateur $login</p>";
        echo "<!-- Infos ";
        print_r($infos);
        echo " -->\n";

        $_SESSION["login"] = $login;
        $_SESSION["passwd"] = $passwd;
        $_SESSION["displayName"] = $displayName;
        $_SESSION["nom"] = $infos["nom"];
        $_SESSION["prenom"] = $infos["prenom"];
        $_SESSION["mail"] = $infos["mail"];
        $_SESSION["role"] = $infos["role"];
    } else {
        if(strlen($login) > 0 && strlen($passwd) > 0) {
            $loginMessage = "<div class='error'><p>Échec de la connexion (utilisateur " . htmlspecialchars($login) . ").</p>";

            //echo "<!-- syndrome = " . syndrome($passwd) . " -->";

            if(estCodeValide($passwd)) {
                $loginMessage .= "<p>Entrez <strong>votre</strong> mot de passe, <strong>pas le code de livraison</strong>&nbsp;!</p>";
            }

            $loginMessage .= "</div>";
        } else {
            $loginMessage = "<p class='error'>Prière de donner un nom d'utilisateur et un mot de passe.</p>";
        }
    }
}

head(false, false, false);

echo $loginMessage;

if(isset($_SESSION["login"])) {
    switch($_SESSION["role"]) {
        case "PROF":
            menu_prof();
            break;
        case "ELEV":
            menu_eleve();
            break;
    }
} else {

?>

<p>Veuillez indiquer vos identifiants habituels de Supélec</p>

<form action="index.php<? if(isset($_GET["code"])) echo "?code=" . $_GET["code"]; ?>" method="post" class="gridform">
    <div><label>Nom d'utilisateur&nbsp;:</label> <input name="login" placeholder="nom_pre" type="text" autofocus></div>
    <div><label>Mot de passe&nbsp;:</label> <input name="password" type="password"></div>
    <div><input type="submit" value="Connexion"></div>
</form>


<?php

}

foot();


function menu_prof() {
    $prefix = baseURL();
    echo <<<EOF
<ul>
<li><a href="ajoutrendu.php">Ajout d'une livraison</a></li>
<li><a href="listerendus.php">Liste des livraisons</a></li>
<li><a href="$prefix/rss.php?id={$_SESSION['login']}">Flux RSS de vos dernières livraisons</a></li>
</ul>
EOF;
}

function menu_eleve() {
    if(isset($_GET["code"])) {
        $code = "value='{$_GET["code"]}'";
        echo "\n<script>$(function() { $('form').submit(); });</script>\n";
    } else {
        $code = "";
    }
    echo <<<EOF

<p>Livrer un compte-rendu&nbsp;:</p>

<form action="rendu.php" method="post" class="gridform">
<div>
<label>Code&nbsp;:</label> <input name="code" {$code} type="text" autofocus>
<input type="submit" value="Accéder au formulaire &gt;&gt;">
</div>
</form>
EOF;
}


?>