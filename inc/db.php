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


class DB {
    static $dbh;
    static $driver;
    
    static function connect() {
        try {
            DB::$dbh = new PDO(Local::$db_dsn, Local::$db_user, Local::$db_password);
            DB::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            DB::$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            
            DB::$driver = DB::$dbh->getAttribute(PDO::ATTR_DRIVER_NAME);
            
            // set charset to UTF-8
            if(DB::$driver == "mysql") {
                DB::$dbh->query("SET NAMES 'utf8'");
            } // nothing to do with SQLite
        } catch(PDOException $e) {
            die("<pre class='error'>Database exception when connecting:\n  " . htmlspecialchars($e->getMessage()) . "</pre>");
        }
    }

    static function request($req, $params = array()) {
        try {
            $sth = DB::$dbh->prepare($req);
            $sth->execute($params);
            return $sth;
        } catch(PDOException $e) {
            die("<div class='error'><p><strong>Un bug est survenu. Merci d'avertir votre encadrant et de lui donner le texte suivant :</strong></p><pre>Database error:\n  Error: " . htmlspecialchars($e->getMessage()) . "\n  Error code: " . htmlspecialchars($e->getCode()) . "\n  Request: " . htmlspecialchars(substr($req, 0, 2000)) . "</pre></div>");            
        }
    }
    
    static function request_one_row($req, $params = array()) {
        $sth = DB::request($req, $params);
        return $sth->fetch();
    }

    static function insert_autoinc($req, $params = array()) {
        $sth = DB::request($req, $params);
        return DB::$dbh->lastInsertId();
    }

}

?>
