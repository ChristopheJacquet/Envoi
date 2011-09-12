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


head("Suppression de fichier d'une livraison", "PROF");

if(DB::request(
        "DELETE FROM fichier WHERE idFichier=?",
        array($_GET["idFichier"]))) {

    echo "<p>Fichier supprimé. <a href=\"voirrendu.php?id={$_GET["idRendu"]}\">Retourner à la livraison.</a></p>\n";
    
}


foot();

?>