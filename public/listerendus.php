<?php

head("Liste des livraisons", "PROF");

$res = DB::request(
        "SELECT idRendu, code, titre FROM rendu WHERE idEnseignant=? ORDER BY date DESC", 
        array($_SESSION["login"]));

echo "<ul>\n";

while($row = $res->fetch()) {
    echo "<li><a href=\"voirrendu.php?id=" . $row->idRendu . "\">" . $row->code . " &mdash; " . htmlspecialchars($row->titre) . "</a></li>\n";
}
echo "</ul>";

foot();

?>
