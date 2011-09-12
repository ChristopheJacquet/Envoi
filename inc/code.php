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


function genereCode($id) {
    // on utilise un code linéaire (26, 10) : l'ID est sur 16 bits, et on ajoute 10 bits de contrôle
    // on prend comme polynôme générateur x10 + x8 + x7 + x5 + x4 + x3 + 1
    // la distance de Hamming doit être bonne car ce code est utilisé pour le RDS !

    // matrice génératrice
    $matrice = array(
        0x077,
        0x2E7,
        0x3AF,
        0x30B,
        0x359,
        0x370,
        0x1B8,
        0x0DC,
        0x06E,
        0x037,
        0x2C7,
        0x3BF,
        0x303,
        0x35D,
        0x372,
        0x1B9);

    // les 10 bits de contrôle sont codés par deux symboles en base 32 à la suite
    // de l'ID.
    // symboles utilisés :
    $symboles = array(
         0 => "Q",
         1 => "1",
         2 => "2",
         3 => "3",
         4 => "4",
         5 => "5",
         6 => "6",
         7 => "7",
         8 => "8",
         9 => "9",
        10 => "A",
        11 => "B",
        12 => "C",
        13 => "D",
        14 => "E",
        15 => "F",
        16 => "G",
        17 => "H",
        18 => "J",
        19 => "K",
        20 => "M",
        21 => "N",
        22 => "P",
        23 => "R",
        24 => "S",
        25 => "T",
        26 => "U",
        27 => "V",
        28 => "W",
        29 => "X",
        30 => "Y",
        31 => "Z");

    $check = 0;
    $dec = $id;
    for($i=0; $i<16; $i++) {
        if($dec & 0x8000) $check ^= $matrice[$i];
        $dec <<= 1;
    }

    // création de l'ID complet
    $id = ($id << 10) | $check;

    $res = "";
    while($id > 0) {
        $res = $symboles[$id & 0x1F] . $res;
        $id >>= 5;
    }

    return $res;
}


function syndrome($code) {
    $matrice = array(
		0x8000,	0x4000,	0x2000,	0x1000,	0x0800,	0x0400,	0x0200,	0x0100,
		0x0080,	0x0040,	0xB700,	0x5B80,	0x2DC0,	0xA1C0,	0xE7C0,	0xC4C0,
		0xD540,	0xDD80,	0x6EC0,	0x8040,	0xF700, 0x7B80,	0x3DC0,	0xA9C0,
		0xE3C0,	0xC6C0);

    $symboles = array(
         0 => "Q",
         1 => "1",
         2 => "2",
         3 => "3",
         4 => "4",
         5 => "5",
         6 => "6",
         7 => "7",
         8 => "8",
         9 => "9",
        10 => "A",
        11 => "B",
        12 => "C",
        13 => "D",
        14 => "E",
        15 => "F",
        16 => "G",
        17 => "H",
        18 => "J",
        19 => "K",
        20 => "M",
        21 => "N",
        22 => "P",
        23 => "R",
        24 => "S",
        25 => "T",
        26 => "U",
        27 => "V",
        28 => "W",
        29 => "X",
        30 => "Y",
        31 => "Z");

    $valeurs = array_flip($symboles);

    $numCode = 0;
    for($i = 0; $i<strlen($code); $i++) {
        if(!array_key_exists($code[$i], $valeurs)) return -1;
        $numCode = ($numCode<<5) | $valeurs[$code[$i]];
    }

    $synd = 0;
    for($i=0; $i<26; $i++) {
            $numCode <<= 1;
            if(($numCode & (1<<26)) != 0) $synd ^= $matrice[$i];
    }

    return $synd;
}

function estCodeValide($code) {
    return syndrome($code) == 0;
}

?>
