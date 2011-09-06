<?php

head("Ajout de fichier à un rendu", "PROF");

if(!isset($_GET["idRendu"])) {
    die("Il faut indiquer un numéro de rendu.");
} else {
    $idRendu = $_GET["idRendu"];

    if(!isset($_POST["nom"]) || !isset($_POST["script"])) {
        // affichage du formulaire
        $nom = isset($_POST["nom"]) ? htmlspecialchars($_POST["nom"]) : "";
        $script = isset($_POST["script"]) ? htmlspecialchars($_POST["script"]) : "";

        $scripts = "";
        foreach(scriptlist() as $s) {
            $scripts .= "<option value=\"$s\">$s</option>";
        }

        echo <<<EOF
<form action="ajoutfichier.php?idRendu=$idRendu" method="post">
<p>Intitulé&nbsp;: <input name="nom" value="$nom" size="50" /></p>
<p>Script vérificateur&nbsp;: <select name="script">$scripts</select></p>
<p>Fichier optionnel&nbsp;: <input type="checkbox" name="optionnel" value="0" /> (optionnel si coché)</p>
<p><input type="submit" value="Créer" /></p>
</form>
EOF;
    } else { // insertion
        DB::request(
                "INSERT INTO fichier (idRendu, nom, script, optionnel) VALUES (?, ?, ?, ?)",
                array($idRendu, $_POST["nom"], $_POST["script"], isset($_POST["optionnel"]) ? 1 : 0));

        echo "<p>Fichier ajouté. <a href=\"voirrendu.php?id=$idRendu\">Retourner au rendu</a>.</p>";
    }
}


foot();


function scriptlist() {
    $list = scandir(Local::$basedir . "/filters/");
    return array_filter($list, "is_script_filename");
}

function is_script_filename($name) {
    return !(substr($name, 0, 1) == ".");
}

?>