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


head("Liste des livraisons", "PROF");

$res = DB::request(
        # subtract 7 months from the date so that a school year fits entirely
        # into a calendary year
        "SELECT idRendu, code, titre, YEAR(DATE_ADD(date, INTERVAL -7 MONTH)) y FROM rendu WHERE idEnseignant=? ORDER BY date DESC", 
        array($_SESSION["login"]));

$curYear = 0;

while($row = $res->fetch()) {
    if($curYear != $row->y) {
        if($curYear > 0) {
            echo "</ul>\n";
        }
        $curYear = $row->y;
        echo "<h2>" . $curYear . "-" . ($curYear+1) . "</h2>\n<ul>\n";
    }
    echo "<li><a href=\"voirrendu.php?id=" . $row->idRendu . "\">" . $row->code . " &mdash; " . htmlspecialchars($row->titre) . "</a></li>\n";
}
echo "</ul>";

foot();

?>
