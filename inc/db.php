<?php

class DB {
    static $dbh;
    
    static function connect() {
        try {
            DB::$dbh = new PDO(Local::$db_dsn, Local::$db_user, Local::$db_password);
            DB::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            DB::$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            DB::$dbh->query("SET NAMES 'utf8'");
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
            die("<div class='error'><p><strong>Un bug est survenu. Merci d'avertir votre encadrant et de lui donner le texte suivant :</strong></p><pre>Database error:\n  Error: " . htmlspecialchars($e->getMessage) . "\n  Error code: " . htmlspecialchars($e->getCode()) . "\n  Request: " . htmlspecialchars(substr($req, 0, 2000)) . "</pre></div>");            
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
