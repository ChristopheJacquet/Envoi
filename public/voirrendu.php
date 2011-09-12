<?php

require("ziptools.php");

head("Livraison", "PROF");

//if($_SESSION["role"] != "PROF") die("Page réservée aux enseignants.");

if(!isset($_GET["id"])) {
    echo "<p>Vous devez indiquer un numéro de livraison. <a href=\"index.php\">Retour à l'accueil</a>.</p>";
} else {
    $idRendu = $_GET["id"];

    $row = DB::request_one_row(
            "SELECT code, titre, COUNT(idFichier) C FROM rendu R LEFT OUTER JOIN fichier F ON R.idRendu=F.idRendu WHERE R.idRendu=? GROUP BY F.idRendu",
            array($idRendu));
    if(! $row) die("Mauvais ID de livraison.");
    echo "<h1>" . htmlspecialchars($row->titre) . " (code " . $row->code . ")</h1>";

    echo "<h2>{$row->C} fichiers</h2>\n<ul>\n";
    $res = DB::request("SELECT nom, script, idFichier, optionnel FROM fichier WHERE idRendu=?", array($idRendu));
    while($row = $res->fetch()) {
        echo "<li>{$row->nom}, {$row->script}, " . ($row->optionnel ? "optionnel" : "imposé") . " [<a href=\"supprfichier.php?idFichier={$row->idFichier}&idRendu={$idRendu}\">supprimer</a>]</li>\n";
    }
    echo "</ul>\n";

    echo "<p><a href=\"ajoutfichier.php?idRendu=$idRendu\">Ajouter un fichier à la livraison</a>.</p>";
    
    $res = DB::request("SELECT date, commentaire, login, idRenduDonne FROM renduDonne WHERE idRendu=? ORDER BY date", array($idRendu));

    echo "<table>\n";
    echo "<tr><th>Élèves</th><th>Date</th><th>Commentaire</th><th>Fichiers</th></tr>\n";

    while($row = $res->fetch()) {
        $idRenduDonne = $row->idRenduDonne;

        echo "<tr><td>";

        // liste des élèves
        $res2 = DB::request("SELECT nom, prenom, email FROM participant WHERE idRenduDonne=?", array($idRenduDonne));
        while($r = $res2->fetch()) {
            echo "<a href=\"mailto:" . $r->email . "\">" . htmlspecialchars($r->prenom) . " " . htmlspecialchars($r->nom) . "</a><br />";
        }
        echo "(" . $row->login . ")";

        echo "</td><td>" . $row->date . "</td><td>" . htmlspecialchars($row->commentaire) . "</td><td>";

        // liste des fichiers

        $res2 = DB::request("SELECT idFichierDonne, nom FROM fichierDonne WHERE idRenduDonne=?", array($idRenduDonne));
        while($r = $res2->fetch()) {
            echo "<a href=\"fichier.php?id=" . $r->idFichierDonne . "\">" . htmlspecialchars($r->nom) . "</a>";
            if($files = list_zip_files($r->idFichierDonne)) {
                echo " (";
                foreach($files as $id => $filename) {
                    echo "<a href=\"zipfichier.php?zipId=" . $r->idFichierDonne . "&fileId=$id\">$filename</a> | ";
                }
                echo ") ";
            }
            echo "<br />";
        }

        echo "</td></tr>";
    }

    echo "</table>";

    echo "<p><a href=\"ziprendu.php?id=$idRendu\">Télécharger tous les fichiers livrés sous forme d'archive ZIP</a>.</p>";
    echo "<p><a href=\"suppr.php?id=$idRendu\">Supprimer cette livraison (irréversible) ; le code vous sera demandé</a>.</p>";
}

foot();

?>
