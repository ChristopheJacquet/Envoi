<?php

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
                $loginMessage .= "<p>Entrez <strong>votre</strong> mot de passe, <strong>pas le code de rendu</strong>&nbsp;!</p>";
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

<form action="index.php" method="post">
    Nom d'utilisateur&nbsp;: <input name="login" placeholder="nom_pre" /><br />
    Mot de passe&nbsp;: <input name="password" type="password" /><br />
    <input type="submit" value="Connexion" />
</form>


<?php

}

foot();


function menu_prof() {
    echo <<<EOF
<ul>
<li><a href="ajoutrendu.php">Ajout d'un rendu</a></li>
<li><a href="listerendus.php">Liste des rendus</a></li>
</ul>
EOF;
}

function menu_eleve() {
    echo <<<EOF
<form action="rendu.php" method="post">
<p>Rendre un compte-rendu&nbsp;:</p>
Code&nbsp;: <input name="code" />
<input type="submit" value="Accéder au formulaire &gt;&gt;" />
</form>
EOF;
}


?>