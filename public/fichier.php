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


session_start();

if(!isset($_SESSION["role"]) || $_SESSION["role"] != "PROF") die("Vous devez être connecté.");

if(!isset($_GET["id"])) die("Vous devez donner un identifiant.");

$req = "SELECT type, contenu, nom FROM fichierDonne WHERE idFichierDonne=" . $_GET["id"];

$row = DB::request_one_row($req);

header("Content-type: " . $row->type);
header("Content-Disposition: inline; filename=\"" . $row->nom ."\"");
echo $row->contenu;


?>