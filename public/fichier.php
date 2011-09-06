<?php

session_start();

if(!isset($_SESSION["role"]) || $_SESSION["role"] != "PROF") die("Vous devez être connecté.");

if(!isset($_GET["id"])) die("Vous devez donner un identifiant.");

$req = "SELECT type, contenu, nom FROM fichierDonne WHERE idFichierDonne=" . $_GET["id"];

$row = DB::request_one_row($req);

header("Content-type: " . $row->type);
header("Content-Disposition: inline; filename=\"" . $row->nom ."\"");
echo $row->contenu;


?>