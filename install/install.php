<?php

require_once "conf/local.php";
require_once "db.php";

DB::connect();


switch(DB::$driver) {
case "mysql":
$statements = array(
<<<EOF
CREATE TABLE IF NOT EXISTS `fichier` (
  `idFichier` int(11) NOT NULL AUTO_INCREMENT,
  `idRendu` int(11) NOT NULL,
  `script` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nom` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `optionnel` tinyint(1) NOT NULL,
  PRIMARY KEY (`idFichier`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOF
,
<<<EOF
CREATE TABLE IF NOT EXISTS `fichierDonne` (
  `idFichierDonne` int(11) NOT NULL AUTO_INCREMENT,
  `idFichier` int(11) NOT NULL,
  `idRenduDonne` int(11) NOT NULL,
  `nom` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `contenu` longblob NOT NULL,
  `type` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`idFichierDonne`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOF
,
<<<EOF
CREATE TABLE IF NOT EXISTS `participant` (
  `idRenduDonne` int(11) NOT NULL,
  `nom` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `prenom` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(40) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOF
,
<<<EOF
CREATE TABLE IF NOT EXISTS `rendu` (
  `idRendu` int(11) NOT NULL AUTO_INCREMENT,
  `idEnseignant` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `titre` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`idRendu`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOF
,
<<<EOF
CREATE TABLE IF NOT EXISTS `renduDonne` (
  `idRenduDonne` int(11) NOT NULL AUTO_INCREMENT,
  `idRendu` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `commentaire` text COLLATE utf8_unicode_ci NOT NULL,
  `login` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`idRenduDonne`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOF
);
break;

case "sqlite":
$statements = array("pragma encoding = 'utf-8';",
<<<EOF
CREATE TABLE IF NOT EXISTS `fichier` (
  `idFichier` INTEGER PRIMARY KEY AUTOINCREMENT,
  `idRendu` INTEGER NOT NULL,
  `script` varchar(100)  DEFAULT NULL,
  `nom` varchar(70)  DEFAULT NULL,
  `optionnel` tinyint(1) NOT NULL
);
EOF
, <<<EOF
CREATE TABLE IF NOT EXISTS `fichierDonne` (
  `idFichierDonne` INTEGER PRIMARY KEY AUTOINCREMENT,
  `idFichier` INTEGER NOT NULL,
  `idRenduDonne` INTEGER NOT NULL,
  `nom` varchar(70)  NOT NULL,
  `contenu` longblob NOT NULL,
  `type` varchar(40)  NOT NULL
);
EOF
, <<<EOF
CREATE TABLE IF NOT EXISTS `participant` (
  `idRenduDonne` INTEGER NOT NULL,
  `nom` varchar(40)  NOT NULL,
  `prenom` varchar(40)  NOT NULL,
  `email` varchar(40)  NOT NULL
);
EOF
, <<<EOF
CREATE TABLE IF NOT EXISTS `rendu` (
  `idRendu` INTEGER PRIMARY KEY AUTOINCREMENT,
  `idEnseignant` varchar(16)  NOT NULL,
  `date` date NOT NULL,
  `titre` varchar(60)  NOT NULL,
  `code` varchar(10)
);
EOF
, <<<EOF
CREATE TABLE IF NOT EXISTS `renduDonne` (
  `idRenduDonne` INTEGER PRIMARY KEY AUTOINCREMENT,
  `idRendu` INTEGER NOT NULL,
  `date` datetime NOT NULL,
  `commentaire` text  NOT NULL,
  `login` varchar(40)  NOT NULL
);
EOF
);
echo "Warning: SQLite support is currently not functional." . PHP_EOL;
break;

default:
die("Unsupported SQL driver: " . DB::$driver) . PHP_EOL;
}

foreach ($statements as $s) {
    DB::request($s);
}


?>
