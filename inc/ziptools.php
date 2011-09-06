<?php

function open_zip_archive($id) {
    $req = "SELECT contenu FROM fichierDonne WHERE idFichierDonne=?";
    $row = DB::request_one_row($req, array($id));

    $result = array();

    $tmpname = tempnam(sys_get_temp_dir(), "phprendu");

    $tmp = fopen($tmpname, "w+");

    fwrite($tmp, $row->contenu);
    fclose($tmp);

    if(is_numeric($zip = zip_open($tmpname))) return false;

    return array("handle" => $zip, "tmpname" => $tmpname);
}

function list_zip_files($id) {
    if(! $r = open_zip_archive($id)) return false;
    $zip = $r["handle"];
    $tmpname = $r["tmpname"];

    $result = Array();
    $idx = 0;
    while($entry = zip_read($zip)) {
        $path = zip_entry_name($entry);
        $components = explode("/", $path);
        $filename = $components[count($components)-1];
        if(substr($filename, 0, 1) != "." && !endswith($filename, ".class") && !endswith($filename, ".prefs")) {
            $result[$idx] = $filename;
        }
        $idx++;
    }

    zip_close($zip);
    unlink($tmpname);

    return $result;
}

function endswith($str, $motif) {
    return $str = substr($str, strlen($str) - strlen($motif)) == $motif;
}

function dump_file_in_zip($zipId, $fileId) {
    $r = open_zip_archive($zipId);
    $zip = $r["handle"];
    $tmpname = $r["tmpname"];

    $idx = 0;
    while($entry = zip_read($zip)) {
        if($idx == $fileId) {
            echo "<pre class=\"brush: java\">";
            echo htmlspecialchars(zip_entry_read($entry, zip_entry_filesize($entry)));
            echo "</pre>";
            //zip_close($zip);
            //unlink($tmpname);
            return;
        }
        $idx++;
    }

    //zip_close($zip);
    //unlink($tmpname);

    echo "File not found in archive.";
}

?>
