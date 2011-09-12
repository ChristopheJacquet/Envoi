<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
        <title>Envoi &ndash; Visualisation de fichier</title>
        <script type="text/javascript" src="syntaxhighlighter/scripts/shCore.js"></script>
	<script type="text/javascript" src="syntaxhighlighter/scripts/shBrushJava.js"></script>
	<script type="text/javascript">
		SyntaxHighlighter.config.clipboardSwf = 'scripts/clipboard.swf';
		SyntaxHighlighter.all();
	</script>
	<link type="text/css" rel="stylesheet" href="syntaxhighlighter/styles/shCore.css"/>
	<link type="text/css" rel="stylesheet" href="syntaxhighlighter/styles/shThemeDefault.css"/>

    </head>

    <body>

<?php

require("ziptools.php");

session_start();

if(!isset($_SESSION["role"]) || $_SESSION["role"] != "PROF") die("Vous devez être connecté.");

if(!isset($_GET["zipId"]) || !isset($_GET["fileId"])) die("Vous devez donner un identifiant.");

dump_file_in_zip($_GET["zipId"], $_GET["fileId"]);

?>


    </body>
</html>