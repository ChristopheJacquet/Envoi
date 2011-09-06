<?php

require_once "../inc/conf/local.php";
require_once "../inc/db.php";

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

DB::connect();

foreach ($statements as $s) {
    DB::request($s);
}


?>
