<?php

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

    $req = "SELECT D.date date, commentaire, login, idRenduDonne, code FROM renduDonne D, rendu R WHERE R.idRendu=? AND R.idRendu=D.idRendu ORDER BY date";

    $res = DB::request($req, array($idRendu));

    $multiCount = Array();

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

        $eleves .= "\nDate de rendu : {$row->date}\n\nCommentaire :\n{$row->commentaire}\n";

        if(isset($multiCount[$login])) {
            $multiCount[$login]++;
            $suffixe = "_" . $multiCount[$login];
        } else {
            $multiCount[$login] = 0;
            $suffixe = "";
        }

        $path = "Rendu_{$row->code}/{$login}{$suffixe}";

        $code = $row->code;

        $zip->addFromString("{$path}/LisezMoi.txt", $eleves);


        // liste des fichiers

        $req = "SELECT idFichierDonne, nom, contenu FROM fichierDonne WHERE idRenduDonne=?";
        $res2 = DB::request($req, array($idRenduDonne));
        while($r = $res2->fetch()) {
            $zip->addFromString("{$path}/{$r->nom}", $r->contenu);
        }

        
    }

    $zip->close();

    if(isset($code)) {
        header("Content-type: application/zip");
        header("Content-Disposition: inline; filename=\"rendu_{$code}.zip\"");
        readfile($tmpname);
    } else {
        echo "Pas de fichier a zipper.";
    }
}





?>