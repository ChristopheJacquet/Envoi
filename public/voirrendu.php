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


require("ziptools.php");

head("NONE", "PROF");


function scriptlist() {
    $list = scandir(Local::$basedir . "/filters/");
    return array_filter($list, "is_script_filename");
}

function is_script_filename($name) {
    return !(substr($name, 0, 1) == ".");
}

function insertFileEditRow($nom = "", $script = "", $optionnel = FALSE, $id = null) {
    $scripts = "";
    foreach(scriptlist() as $s) {
        $sel = ($s == $script) ? "selected" : "";
        $scripts .= "<option value=\"$s\" $sel>$s</option>";
    }
    
    echo "<tr><td><input name='nom' required autofocus value='" . htmlspecialchars($nom) . 
            "'></td><td><select name='script'>$scripts</select></td>" . 
            "<td><input type='checkbox' name='optionnel' " . ($optionnel ? "checked" : "") . ">" .
            "</td><td>";
    
    if($id == null) {
        echo "<input type='submit' value='Ajouter'>";
    } else {
        echo "<input type='submit' value='Valider'><input type='hidden' name='idFichier' value='$id'>";
    }
    
    echo "</tr>\n";
}




if(!isset($_GET["id"])) {
    echo "<p>Vous devez indiquer un numéro de livraison. <a href=\"index.php\">Retour à l'accueil</a>.</p>";
} else {
    $idRendu = $_GET["id"];
    
    if(isset($_GET["do"])) {
        $do = $_GET["do"];
        
        if($do == "notifen") {
            DB::request("UPDATE rendu SET notification=? WHERE idRendu=?", array($_SESSION["mail"], $idRendu));
            
        } elseif($do == "notifdis") {
            DB::request("UPDATE rendu SET notification=NULL WHERE idRendu=?", array($idRendu));
            
        } elseif($do == "filemod") {
            if(isset($_POST["nom"]) && isset($_POST["script"])) {
                if(isset($_POST["idFichier"])) {
                    # modification
                    if( DB::request(
                            "UPDATE fichier SET nom=?, script=?, optionnel=? WHERE idRendu=? AND idFichier=?",
                            array( $_POST["nom"], $_POST["script"], isset($_POST["optionnel"]) ? 1 : 0, $idRendu, $_POST["idFichier"] ) ) ) {
                        echo "<div class='info'>Spécification de fichier modifiée.</div>\n";
                    }
                } else {
                    # création
                    if( DB::request(
                        "INSERT INTO fichier (idRendu, nom, script, optionnel) VALUES (?, ?, ?, ?)",
                        array($idRendu, $_POST["nom"], $_POST["script"], isset($_POST["optionnel"]) ? 1 : 0)) ) {
                        
                        echo "<div class='info'>Nouvelle spécification de fichier ajoutée.</div>\n";
                    }
                }
            } else {
                echo "<div class='info'>Pas assez d'indications dans la spécification de fichier.</div>\n";
            }
            
        } elseif($do == "filedel") {
            if(isset($_GET["idFichier"])) {
                if(DB::request("DELETE FROM fichier WHERE idFichier=?", array($_GET["idFichier"]))) {
                    echo "<div class='info'>Spécification de fichier supprimée.</div>\n";
                }
            } else {
                echo "<div class='info'>Manque le paramètre idFichier.</div>\n";
            }
        }
    }

    $row = DB::request_one_row(
            "SELECT code, titre, COUNT(idFichier) C, notification FROM rendu R LEFT OUTER JOIN fichier F ON R.idRendu=F.idRendu WHERE R.idRendu=? GROUP BY F.idRendu",
            array($idRendu));
    if(! $row) die("Mauvais ID de livraison.");
    echo "<h1>Livraison&nbsp;: " . htmlspecialchars($row->titre) . "</h1>\n";
    echo "<h2>Spécifications</h2>\n";

    echo "<table class='rendu_summary'>\n";
    
    # Code
    echo "<tr><td><img src='img/icon_code.png' alt='Code'></td><td>Code</td><td>" . $row->code . "</td></tr>\n";
    
    # URL
    $url = baseURL() . "?code=" . $row->code;
    $notification = $row->notification;
    echo "<tr><td><img src='img/icon_url.png' alt='URL'></td><td>URL</td> <td><a href='{$url}'>{$url}</a></td></tr>\n";

    # Fichiers
    echo "<tr><td><img src='img/icon_file.png' alt='Files'></td><td>Fichiers ({$row->C})</td>\n<td>\n";
    $res = DB::request("SELECT nom, script, idFichier, optionnel FROM fichier WHERE idRendu=? ORDER BY idFichier", array($idRendu));
    echo "<form action='voirrendu.php?id=$idRendu&amp;do=filemod' method='POST'>\n";
    echo "<table class='filelist'>";
    echo "<tr><th>Intitulé</th><th>Script vérificateur</th><th>Optionnel</th><th></th></tr>";
    $formInserted = FALSE;
    while($row = $res->fetch()) {
        if(isset($_GET["modFichier"]) && $_GET["modFichier"] == $row->idFichier) {
            insertFileEditRow($row->nom, $row->script, $row->optionnel, $row->idFichier);
            $formInserted = TRUE;
        } else {
            echo "<tr><td>{$row->nom}</td><td>{$row->script}</td><td>" . ($row->optionnel ? "✓" : "") . "</td><td>" . 
                    "<a href='voirrendu.php?id=$idRendu&amp;modFichier={$row->idFichier}' class='btn_mod_file'><img src='img/icon_edit.png' alt='Modifier'></a>" .
                    "<a href='voirrendu.php?id=$idRendu&amp;idFichier={$row->idFichier}&amp;do=filedel' class='btn_del_file'><img src='img/icon_trash.png' alt='Supprimer'></a>" .
                    "</td></tr>\n";
        }
    }
    if(! $formInserted) {
        insertFileEditRow();
    }
    
    echo "</table>\n";
    echo "</form>\n";

    echo "</td></tr>\n";

    # Notifications
    echo "<tr><td><img src='img/icon_notification.png' alt='Notification'></td><td>Notifications</td>";
    if(is_null($notification)) {
        echo "<td><span class='state_off'>Désactivées</span> <a href='voirrendu.php?id=$idRendu&amp;do=notifen'>⇒ Activer</a></td>";
    } else {
        echo "<td><span class='state_on'>Activées</span> vers&nbsp;: $notification <a href='voirrendu.php?id=$idRendu&amp;do=notifdis'>⇒ Désactiver</a></td>";
    }
    echo "</tr>\n";
    echo "</table>\n";

    echo "<script>connectDialog($('#btn_add_file'), 'ajoutfichier.php?idRendu={$idRendu}');</script>";
    
    echo "<h2>Livraisons effectuées</h2>\n";
    
    $obj = DB::request_one_row("SELECT COUNT(DISTINCT login) nb FROM renduDonne WHERE idRendu=?", array($idRendu));
    echo "<p>{$obj->nb} groupes ont effectué une livraison.</p>";
    echo "<p><a href=\"ziprendu.php?id=$idRendu\">⇒ Télécharger tous les fichiers livrés sous forme d'archive ZIP</a></p>";

    
    $res = DB::request("SELECT date, commentaire, login, idRenduDonne FROM renduDonne WHERE idRendu=? ORDER BY date", array($idRendu));

    echo "<table>\n";
    echo "<tr><th>Élèves</th><th>Date</th><th>Commentaire</th><th>Fichiers</th></tr>\n";

    $idCount = 0;
    
    while($row = $res->fetch()) {
        $idRenduDonne = $row->idRenduDonne;

        echo "<tr><td>";

        // liste des élèves
        $res2 = DB::request("SELECT nom, prenom, email FROM participant WHERE idRenduDonne=?", array($idRenduDonne));
        while($r = $res2->fetch()) {
            echo "<a href=\"mailto:" . $r->email . "\">" . htmlspecialchars($r->prenom) . " " . htmlspecialchars($r->nom) . "</a><br />";
        }
        echo "(" . $row->login . ")";

        echo "</td><td>" . $row->date . "</td><td>" . str_replace("\n", "<br>", htmlspecialchars($row->commentaire)) . "</td><td>";

        // liste des fichiers

        $res2 = DB::request("SELECT idFichierDonne, nom FROM fichierDonne WHERE idRenduDonne=?", array($idRenduDonne));
        while($r = $res2->fetch()) {
            echo "<a href=\"fichier.php?id=" . $r->idFichierDonne . "\">" . htmlspecialchars($r->nom) . "</a>";
            if($files = list_zip_files($r->idFichierDonne)) {
                $htmlid = "zip" + $idCount;
                $idCount++;
                echo " <a href='#' onclick='$(\"#{$htmlid}\").toggle(); return false;'>▶</a> <span id='{$htmlid}' class='closedsection'>(";
                foreach($files as $id => $filename) {
                    echo "<a href=\"zipfichier.php?zipId=" . $r->idFichierDonne . "&fileId=$id\">$filename</a> | ";
                }
                echo ")</span> ";
            }
            echo "<br />";
        }

        echo "</td></tr>";
    }

    echo "</table>";

    echo "<p>Contenu de tous les fichiers ZIP&nbsp;: <a href=\"#\" onclick='$(\".closedsection\").show(); return false;'>déployer</a> | <a href=\"#\" onclick='$(\".closedsection\").hide(); return false;'>escamoter</a>.</p>";
    echo "<h2>Suppression</h2>\n";
    echo "<p><a href=\"suppr.php?id=$idRendu\">Supprimer cette livraison (irréversible) ; le code vous sera demandé</a>.</p>";
}

foot();

?>
