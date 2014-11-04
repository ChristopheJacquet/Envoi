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

    supprimeLivraison($idRendu, $_POST["code"]);
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
