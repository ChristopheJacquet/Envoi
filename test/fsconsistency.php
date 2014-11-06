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
 */

require("tools.php");

$path = Local::$basedir . "/data/";

$filelist = array();

function buildfilelist($prefix) {
    global $path, $filelist;
    
    $files = scandir($path . $prefix);

    foreach($files as $f) {
        if(strpos($f, ".") === 0) continue;
        if(is_dir($path . $f)) {
            buildfilelist($prefix . $f . "/");
        } else {
            $filelist[ $path . $prefix . $f ] = TRUE;
        }
    }
}

buildfilelist("");


echo "Checking whether the filesystem is consistent with the database...\n";

$delete = isset($argv[1]) && $argv[1] === "delete_orphans";

if($delete) echo "Will be deleting orphans.\n";

$res = DB::request(
        "SELECT idFichierDonne, idFichier, idRenduDonne, idRendu FROM fichierDonne NATURAL JOIN renduDonne");

#var_dump($filelist);

$countOk = 0;
$countMissing = 0;
$countOrphaned = 0;

while($row = $res->fetch()) {
    fileIdToPath($row->idFichierDonne, $p, $n);
    
    $fn = $p . $n;
    
    if(isset($filelist[$fn])) {
        unset($filelist[$fn]);
        $countOk++;
    } else {
        echo "Missing file: " . $fn . "   idRendu=" . $row->idRendu . ", idFichier=" . 
                $row->idFichier . ", idRenduDonne=" . $row->idRenduDonne . " \n";
        $countMissing++;
    }
}

# At this point, the only remaining files are orphans
foreach($filelist as $k => $v) {
    echo "Orphaned file: " . $k . "\n";
    if($delete) {
        if( unlink($k) ) {
            echo "Deleted $k\n";
        }
    }
    $countOrphaned++;
}

echo "\nOK: " . $countOk . ", missing: " . $countMissing . ", orphaned: " . $countOrphaned . "\n";