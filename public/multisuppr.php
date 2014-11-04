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

    (c) Christophe Jacquet, 2014.
    (c) Arpad Rimmel, 2014.
 */


head("Suppression de livraisons", "PROF");

if(isset($_POST["confirmation"]) && isset($_POST["liste"])) {
    if($_POST["confirmation"] != "OUI") {
        echo "<p>Il faut écrire OUI pour lancer la suppression...</p>";
    } else {
        foreach($_POST["liste"] as $id) {
            supprimeLivraison($id);
            echo "<hr>";
        }
        
        echo "<h1>D'autres suppressions ?</h1>";
    }
}

?>

<p>Cochez les livraisons à supprimer.</p>

<form method="post" action="multisuppr.php" id="multisuppr">

<?php listeLivraisons("<input type='checkbox' name='liste[]' value='{id}'>{titre}"); ?>


<h2>Valider la suppression</h2>

<p>1) Êtes-vous sûr ? Écrivez OUI ici : <input name="confirmation"></p>

<p>2) Cliquez ici : <input type="submit"></p>

</form>
    

<?php 

foot();

?>
