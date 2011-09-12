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


head("Ajout de fichier à une livraison", "PROF");

if(!isset($_GET["idRendu"])) {
    die("Il faut indiquer un numéro de livraison.");
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

        echo "<p>Fichier ajouté. <a href=\"voirrendu.php?id=$idRendu\">Retourner à la livraison</a>.</p>";
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