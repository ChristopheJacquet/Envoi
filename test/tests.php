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

    (c) Christophe Jacquet, 2009-2014.
 */

include("tools.php");

echo "*** fileIdToPath ***\n";

$ids = array(0x12, 0x1212, 0x12345, 0x78654312);

foreach($ids as $i) {
    fileIdToPath($i, $p, $d);
    echo sprintf("%8x: ", $i);
    echo "$p $d\n";
}


echo "*** create dirs ***\n";
//Local::$basedir

echo mkdir("/tmp/a/b", 0777, true);
echo mkdir("/tmp/a/c", 0777, true);
echo mkdir("/tmp/a/c/d", 0777, true);