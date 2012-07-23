<?php

/*

SELECT code, titre, GROUP_CONCAT(CONCAT(prenom, " ", nom) SEPARATOR ', ') noms, renduDonne.date, idEnseignant FROM renduDonne JOIN rendu JOIN participant ON renduDonne.idRendu=rendu.idRendu AND renduDonne.idRenduDonne=participant.idRenduDonne WHERE renduDonne.date >= '2012-07-23' GROUP BY renduDonne.idRenduDonne ORDER BY rendu.idRendu

 */

if(! isset($_GET["id"])) {
    echo "Id enseignant manquant.";
    exit();
}

$id = $_GET["id"];

$res = DB::request("SELECT code, titre, GROUP_CONCAT(CONCAT(prenom, ' ', nom) SEPARATOR ', ') noms, renduDonne.date FROM renduDonne JOIN rendu JOIN participant ON renduDonne.idRendu=rendu.idRendu AND renduDonne.idRenduDonne=participant.idRenduDonne WHERE idEnseignant=? GROUP BY renduDonne.idRenduDonne ORDER BY renduDonne.date DESC LIMIT 0,50", array($id));


 date_default_timezone_set("Europe/Berlin");

echo <<<END
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">

<channel>
<title>Livraisons pour {$id}</title>
END;

while($obj = $res->fetch()) {
    $date = date(DATE_RFC822, strtotime($obj->date));
    
    echo <<<END
<item>
<title>{$obj->code} - {$obj->titre}</title>
<description>{$obj->date} : livraison par {$obj->noms}</description>
<pubDate>{$date}</pubDate>
</item>
END;
}

echo <<<END
</channel>
</rss>
END;

?>
