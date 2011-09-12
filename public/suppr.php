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


head("Suppression de livraison", "PROF");

if(isset($_POST["code"])) {
    $idRendu = $_GET["id"];

    echo "<p>Suppression de la livraison #" . $idRendu . "</p>";

    # rendu
    $res = DB::request(
            "DELETE FROM rendu WHERE idRendu=? AND idEnseignant=? AND code=?", 
            array($idRendu, $_SESSION["login"], $_POST['code']));

    if($res->rowCount() < 1) {
        die(<<<END
<p>$idRendu : livraison inexistante, ou n'appartenant pas à l'utilisateur {$_SESSION["login"]}, ou mauvais code.</p>
<p><a href="voirrendu.php?id=$idRendu">Retour au rendu</a></p>
END
);
    } else {
        echo "<p>Supprimé la spécification de livraison.</p>";
    }


    # fichierDonne
    $res = DB::request(
            "DELETE FROM fichierDonne WHERE idFichier IN (SELECT idFichier FROM fichier WHERE idRendu=?)",
            array($idRendu));

    echo "<p>Supprimé " . ($res->rowCount()) . " fichiers fournis</p>";


    # participant
    $res = DB::request(
            "DELETE FROM participant WHERE idRenduDonne IN (SELECT idRenduDonne FROM renduDonne WHERE idRendu=?)",
            array($idRendu));

    echo "<p>Supprimé " . ($res->rowCount()) . " participants</p>";


    # renduDonne
    $res = DB::request(
            "DELETE FROM renduDonne WHERE idRendu=?",
            array($idRendu));

    echo "<p>Supprimé " . ($res->rowCount()) . " livraisons</p>";


    # fichier
    $res = DB::request(
            "DELETE FROM fichier WHERE idRendu=?",
            array($idRendu));

    echo "<p>Supprimé " . ($res->rowCount()) . " spécifications de fichiers";
} else {
    $url = htmlspecialchars($_SERVER['REQUEST_URI']);
    echo <<<FIN
<form method="post" action="$url">
<p>
Code :
<input name="code"></input>
<input type="submit"></input>
</p>
</form>
FIN;
}

foot();

?>
