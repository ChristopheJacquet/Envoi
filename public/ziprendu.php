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

if(!isset($_GET["id"])) {
    die("Vous devez donner un identifiant.");
} else {
    // fichier zip temporaire
    $tmpname = tempnam(sys_get_temp_dir(), "phprendu");
    $zip = new ZipArchive;
    if(! $zip->open($tmpname) === TRUE) {
        die("Failed to open ZIP file");
    }

    $idRendu = $_GET["id"];

    $req = "SELECT D.date date, commentaire, login, idRenduDonne, code, titre FROM renduDonne D, rendu R WHERE R.idRendu=? AND R.idRendu=D.idRendu ORDER BY date";

    $res = DB::request($req, array($idRendu));

    $multiCount = Array();

    // used for translitteration
    setlocale(LC_ALL, Local::$locale);
    
    while($row = $res->fetch()) {
        $idRenduDonne = $row->idRenduDonne;

        $eleves = "Étudiants :\n";
        $login = $row->login;
        
        // liste des élèves
        $req = "SELECT nom, prenom, email FROM participant WHERE idRenduDonne=?";
        $res2 = DB::request($req, array($idRenduDonne));
        while($r = $res2->fetch()) {
            $eleves .= " - {$r->prenom} {$r->nom} <{$r->email}>\n";
        }

        $eleves .= "\nDate de livraison : {$row->date}\n\nCommentaire :\n{$row->commentaire}\n";

        if(isset($multiCount[$login])) {
            $multiCount[$login]++;
            $suffixe = "_" . $multiCount[$login];
        } else {
            $multiCount[$login] = 0;
            $suffixe = "";
        }
        
        $path = "Livraison_{$row->code}/{$login}{$suffixe}";

        $code = $row->code;
        
                
        $ascii_title = preg_replace("/[^A-Za-z0-9]/", "_", iconv('UTF-8', 'ASCII//TRANSLIT', $row->titre));


        $zip->addFromString("{$path}/LisezMoi.txt", $eleves);


        // liste des fichiers

        $req = "SELECT idFichierDonne, nom FROM fichierDonne WHERE idRenduDonne=?";        
        $res2 = DB::request($req, array($idRenduDonne));
        while($r = $res2->fetch()) {
            fileIdToPath($r->idFichierDonne, $fspath, $fsname);
            if(is_file($fspath . $fsname)) {
                $zip->addFile($fspath . $fsname, "{$path}/{$r->nom}");
                /*
                echo "addFile $fspath $fsname: ";
                echo "  ==> ";
                echo filesize($tmpname);
                echo "\n";
                 * 
                 */
            } else {
                $zip->addFromString("{$path}/{$r->nom}", "File ID {$r->idFichierDonne} missing; this is probably a bug.");
            }
        }

        
    }

    $zip->close();

    if(isset($code)) {
        header("Content-type: application/zip");
        header("Content-Disposition: inline; filename=\"livraison_{$code}_{$ascii_title}.zip\"");
        readfile($tmpname);
    } else {
        echo "Pas de fichier a zipper.";
    }
}





?>