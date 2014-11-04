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

session_start();

if(!isset($_SESSION["role"])) {
    header("HTTP/1.1 401 Vous devez etre connecte.");
    die();
}

if(!isset($_GET["method"])) {
    header("HTTP/1.1 400 Methode manquante.");
    die();
}

$method = $_GET["method"];

if($method == "is_valid_email") {
    if(!isset($_GET["email"])) {
        header("HTTP/1.1 400 Parametre email manquant.");
        die();
    }
    
    $infos = array();
    $valid = Local::is_valid_email($_SESSION["login"], $_GET["email"], $infos);
    
    $infos["valid"] = $valid ? 1 : 0;
    
    echo json_encode($infos);
}

?>