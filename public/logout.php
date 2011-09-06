<?php

session_start();
session_destroy();

head("Déconnexion", false, false);

?>

<p>Déconnecté.</p>

<p><a href="index.php">Retour à l'écran de connexion</a>.</p>

<?php

foot();

?>
