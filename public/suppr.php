<?php

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
