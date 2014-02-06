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


function open_zip_archive($id) {
    /*
     * No longer necessary since files are already on the filesystem
    $req = "SELECT contenu FROM fichierDonne WHERE idFichierDonne=?";
    $row = DB::request_one_row($req, array($id));

    $result = array();

    $tmpname = tempnam(sys_get_temp_dir(), "phprendu");

    $tmp = fopen($tmpname, "w+");

    fwrite($tmp, $row->contenu);
    fclose($tmp);
    */
    
    fileIdToPath($id, $fspath, $fsname);
    
    if(is_numeric($zip = zip_open($fspath . $fsname))) return false;

    return array("handle" => $zip);
}

function list_zip_files($id) {
    if(! $r = open_zip_archive($id)) return false;
    $zip = $r["handle"];

    $result = Array();
    $idx = 0;
    while($entry = zip_read($zip)) {
        $path = zip_entry_name($entry);
        $components = explode("/", $path);
        $filename = $components[count($components)-1];
        /*
         * Remove
         * - filenames that begin with .
         * - .class
         * - .prefs
         * - paths ending with / (directory names)
         * - paths that contain .metadata
         */
        if(substr($filename, 0, 1) != "." && !endswith($filename, ".class") && !endswith($filename, ".prefs") && !endswith($path, "/") && strpos($path, ".metadata")===FALSE) {
            $result[$idx] = $filename;
            $idx++;
        }
    }

    zip_close($zip);
    
    return $result;
}

function endswith($str, $motif) {
    return $str = substr($str, strlen($str) - strlen($motif)) == $motif;
}

function dump_file_in_zip($zipId, $fileId) {
    $r = open_zip_archive($zipId);
    $zip = $r["handle"];

    $idx = 0;
    while($entry = zip_read($zip)) {
        if($idx == $fileId) {
            echo "<pre class=\"brush: java\">";
            echo htmlspecialchars(zip_entry_read($entry, zip_entry_filesize($entry)));
            echo "</pre>";
            return;
        }
        $idx++;
    }

    echo "File not found in archive.";
}

?>
