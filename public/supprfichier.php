<?php

head("Suppression de fichier d'une livraison", "PROF");

if(DB::request(
        "DELETE FROM fichier WHERE idFichier=?",
        array($_GET["idFichier"]))) {

    echo "<p>Fichier supprimé. <a href=\"voirrendu.php?id={$_GET["idRendu"]}\">Retourner à la livraison.</a></p>\n";
    
}


foot();

?>