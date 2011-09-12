<?php

require("code.php");
head("Ajout de livraison", "PROF");

if(isset($_POST["titre"])) {
    $idRendu = DB::insert_autoinc(
            "INSERT INTO rendu (idEnseignant, date, titre) VALUES (?, CURRENT_DATE, ?)",
            array($_SESSION["login"], $_POST["titre"]));

    $code = genereCode($idRendu);

    DB::request("UPDATE rendu SET code=? WHERE idRendu=?", array($code, $idRendu));

    echo "<p>Le code pour la livraison « " . htmlspecialchars($_POST["titre"]) . " » est <strong>$code</strong>.</p>\n";

    if($_POST["clone"] != "no") {
        $idParent = $_POST["clone"];
        echo "<p>Clonage de la livraison #" . $idParent . "...</p>\n";
        $res = DB::request("SELECT script, nom, optionnel FROM fichier WHERE idRendu=?", array($idParent));
        while($obj = $res->fetch()) {
            DB::request(
                    "INSERT INTO fichier (idRendu, script, nom, optionnel) VALUES (?, ?, ?, ?)",
                    array($idRendu, $obj->script, $obj->nom, $obj->optionnel));
            echo "<p>Ajout par clonage du fichier '{$obj->script}', '{$obj->nom}', '{$obj->optionnel}'</p>";
        }

    } else {
        echo "<p>Vous voulez probablement <a href=\"ajoutfichier.php?idRendu=$idRendu\">ajouter un fichier à cette livraison...</p>\n";
    }

    echo "<p><a href=\"index.php\">Retour à l'accueil</a>.</p>\n";


} else {
    $res = DB::request(
            "SELECT titre, idRendu FROM rendu WHERE idEnseignant=? ORDER BY date DESC",
            array($_SESSION['login']));
    $opt = "";
    while($obj = $res->fetch()) {
        $opt .= "<option value='{$obj->idRendu}'>{$obj->titre}</a>\n";
    }

    echo <<<EOF

<form action="ajoutrendu.php" method="post">
Titre&nbsp;: <input name="titre" /><br />
Clonage&nbsp;: <select name="clone">
    <option value="no">Non</option>
    $opt
    </select><br />
<input type="submit" value="Créer" />
</form>

EOF;
}

foot();



?>
