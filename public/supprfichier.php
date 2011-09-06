<?php

head("Suppression de fichier d'un rendu", "PROF");

if(DB::request(
        "DELETE FROM fichier WHERE idFichier=?",
        array($_GET["idFichier"]))) {

    echo "<p>Fichier supprimé. <a href=\"voirrendu.php?id={$_GET["idRendu"]}\">Retourner au rendu.</a></p>\n";
    
}


foot();

?>