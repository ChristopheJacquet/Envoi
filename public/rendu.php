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

require_once('phpmailer/class.phpmailer.php');

head("Livraison de compte-rendu");

//    print_r($_POST);

if(!isset($_POST["code"])) {
    echo "<p>Erreur, un code de retour doit être donné. <a href=\"index.php\">Retour à l'accueil</a>.</p>";
} else {
    // un code existe, mais le formulaire reste à remplir
    $code = $_POST["code"];

    $res = DB::request("SELECT idRendu, titre FROM rendu WHERE code=?", array($code));
    $all = $res->fetchAll();
    if(count($all) < 1) {
        echo "<p>Mauvais code de livraison. Veuillez vérifier votre saisie. <a href=\"index.php\">Retour au formulaire</a>.</p>";
    } else {
        // OK vérifications faites
        $obj = $all[0];
        $titre = $obj->titre;
        $idRendu = $obj->idRendu;


        $afficheFormulaire = true;
        if(isset($_POST["nom1"])) {
            // un code existe, et le formulaire a été rempli

            $conformes = TRUE;
            // vérifie que les fichiers sont conformes
            $rows = DB::request(
                    "SELECT script, nom, idFichier, optionnel FROM fichier WHERE idRendu=?",
                    array($idRendu));

            while($r = $rows->fetch()) {
                $idFichier = $r->idFichier;
                if(isset($_FILES["fichier{$idFichier}"])) {
                    $file = $_FILES["fichier{$idFichier}"];


                    //foreach($_FILES as $formFileId => $file) {
                    if($file['error'] == UPLOAD_ERR_OK && $file['size'] >  0) {
                        $tmpName  = $file['tmp_name'];

                        //$idFichier = substr($formFileId, 7);

                        //echo "idFichier: $idFichier";


                        //echo "   nom='{$row->nom}' script={$row->script}  ";

                        $output = array();
                        $retval = 0;
                        exec(escapeshellcmd(Local::$basedir . "/filters/" . $r->script) . " " . escapeshellarg($tmpName), $output, $retval);
                        if($retval != 0) {
                            if($conformes) echo "<div class=\"error\">\n";
                            echo "<p>Le fichier « {$r->nom} » n'est pas conforme: </p><pre>" . implode("\n", $output) . "</pre>";
                            $conformes = FALSE;
                        }
                    } else {  // taille à zéro => fichier absent
                        if(! $r->optionnel) {
                            if($conformes) echo "<div class=\"error\">\n";
                            
                            if($file['error'] == UPLOAD_ERR_NO_FILE) {
                                echo "<p>Le fichier « {$r->nom} » est absent.";
                            } elseif($file['error'] == UPLOAD_ERR_OK && $file['size'] == 0) {
                                echo "<p>Le fichier « {$r->nom} » est vide.";
                            } elseif($file['error'] == UPLOAD_ERR_INI_SIZE || $file['error'] == UPLOAD_ERR_FORM_SIZE) {
                                echo "<p>Fichier « {$r->nom} » trop gros. ";
                                echo "Taille maximum : min(" . ini_get("post_max_size") . ", " . ini_get("upload_max_filesize") . ").</p>\n";
                            } else {
                                echo "<p>Le fichier « {$r->nom} » n'a pu être téléversé. ";
                                echo "Merci de contacter votre encadrant en lui indiquant le code d'erreur : ";
                                echo "<strong>{$file['error']}</strong>.</p>\n";
                                # codes d'erreur ici : http://fr.php.net/manual/en/features.file-upload.errors.php
                            }
                            $conformes = FALSE;
                        }
                    }
                } else {
                    if(! $r->optionnel) {
                        if($conformes) echo "<div class=\"error\">\n";
                        echo "<p>Le fichier « {$r->nom} » manque.</p>\n";
                        $conformes = FALSE;
                    }
                }
            }

            if($conformes) {
                // A ce state, la livraison est a priori conforme. Essayons de
                // construire l'e-mail, ce qui va permettre de verifier si les
                // adresses sont correctes

                // Préparation e-mail
                $mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
                $mail->IsSMTP(); // telling the class to use SMTP
                $mail->Host       = Local::$smtp_relay; // SMTP server
                $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
                $mail->SetFrom(Local::$from_email, Local::$from_name);
                $mail->CharSet = "UTF-8";
                $mail->Subject = 'Livraison de fichiers';
                $mail->Body = "Livraison du travail pour : " . $obj->titre . "\n\n" .
                        "Votre commentaire :\n" .
                        $_POST["commentaire"] . "\n\n" .
                        "Les fichiers suivants ont ete recus :\n";
                //$mail->MsgHTML($mail->AltBody);
                // Fin préparation e-mail

                // traitement participants
                for($i = 1; $i<=3; $i++) {
                    if(isset($_POST["nom" . $i]) && trim($_POST["nom" . $i]) != "" && trim($_POST["email" . $i]) != "") {
                        // Ajout à l'e-mail
                        $email = $_POST["email" . $i];
                        echo "<!-- ";
                        try {
                            $mail->AddAddress($email, $email);
                            echo " -->";
                            echo "<p>Destinataire de l'e-mail de confirmation : $email</p>";
                        } catch (phpmailerException $e) {
                            echo " -->";
                            echo "<div class=\"error\">\n";
                            echo "<p>Adresse e-mail invalide : « $email »<br />" . trim($e->errorMessage()) . "</p>\n";
                            $conformes = FALSE;
                            break;
                        }
                        
                    }
                }

            }

            if(!$conformes) {
                echo "<p><strong>Livraison non effectuée. Vous devez recommencer.</strong></p></div>";
            }

            if($conformes) {

                $commentaire = $_POST["commentaire"];
                $login = $_SESSION["login"];

                $req = "INSERT INTO renduDonne (date, commentaire, idRendu, login) VALUES (NOW(), ?, ?, ?)";

                $idRenduDonne = DB::insert_autoinc($req,
                        array($commentaire, $idRendu, $login));


                // traitement participants
                for($i = 1; $i<=3; $i++) {
                    if(isset($_POST["nom" . $i]) && trim($_POST["nom" . $i]) != "" && trim($_POST["email" . $i]) != "") {
                        $nom = $_POST["nom" . $i];
                        $prenom = $_POST["prenom" . $i];
                        $email = $_POST["email" . $i];
                        $req = "INSERT INTO participant (idRenduDonne, nom, prenom, email) VALUES (?, ?, ?, ?)";
                        DB::request($req, array($idRenduDonne, $nom, $prenom, $email));
                    }
                }

                // traitement fichiers
                //print_r($_FILES);
                foreach($_FILES as $formFileId => $file) {
                    if($file['size'] >  0) {
                        $fileName = $file['name'];
                        $tmpName  = $file['tmp_name'];
                        $fileSize = $file['size'];
                        $fileType = $file['type'];

                        $fp      = fopen($tmpName, 'r');
                        $content = fread($fp, filesize($tmpName));
                        #$content = addslashes($content);  No longer needed with PDO
                        fclose($fp);

                        $idFichier = substr($formFileId, 7);

                        $req = "INSERT INTO fichierDonne (idRenduDonne, nom, contenu, type, idFichier) ".
                            "VALUES (?, ?, ?, ?, ?)";

                        //echo "<!-- $req -->";

                        DB::request($req, array($idRenduDonne, $fileName, $content, $fileType, $idFichier));

                        if(true)
                            echo "<p>Fichier $fileName téléchargé</p>";

                        // Ajout à l'e-mail
                        $mail->AddAttachment($tmpName, $fileName);


                    } else {
                        echo "<p>Fichier " . $file["name"] . " sauté car de taille zéro.</p>";
                    }
                }

                echo "<!-- ";
                try {
                    $mail->Send();
                    echo " -->";
                    echo "<p>E-mail de confirmation envoyé.</p>";
                } catch (phpmailerException $e) {
                    echo " -->";
                    echo $e->errorMessage(); //Pretty error messages from PHPMailer
                    
                } catch (Exception $e) {
                    echo " -->";
                    echo $e->getMessage(); //Boring error messages from anything else!
                    
                }

                // Suppression des fichiers temporaires
                foreach($_FILES as $formFileId => $file) {
                    if($file["size"] > 0)
                        unlink($file["tmp_name"]);
                }
                

                echo "<div class='success'><p>Compte-rendu livré avec succès. <a href=\"index.php\">Retour à l'accueil</a>.</p></div>";


                $afficheFormulaire = false;
            }
        }

        if($afficheFormulaire) {

            // Fichiers à fournir
            $zoneFichiers = "";

            $res = DB::request("SELECT nom, idFichier, optionnel FROM fichier WHERE idRendu=?", array($idRendu));

            while($obj = $res->fetch()) {
               $nom = $obj->nom;
               $idFichier = $obj->idFichier;
               //$fname = isset($_FILES["fichier$idFichier"]) ? "value=\"" . $_FILES["fichier" . $idFichier]["name"] . "\" " : "";
               $zoneFichiers .= "<li>$nom" . ($obj->optionnel ? " (optionnel) " : "") . "&nbsp;: <input name=\"fichier$idFichier\" type=\"file\" id=\"fichier$idFichier\" /></li>\n";
          }

          $fnom1 = isset($_POST["nom1"]) ? 'value="' . htmlspecialchars($_POST["nom1"]) . '" ' : "";
          $fprenom1 = isset($_POST["prenom1"]) ? 'value="' . htmlspecialchars($_POST["prenom1"]) . '" ' : "";
          $femail1 = isset($_POST["email1"]) ? 'value="' . htmlspecialchars($_POST["email1"]) . '" ' : "";

          if(!isset($_POST["nom1"]) && !isset($_POST["prenom1"]) && !isset($_POST["email1"])) {
              $fnom1 = "value=\"" . $_SESSION["nom"] . "\" ";
              $fprenom1 = "value=\"" . $_SESSION["prenom"] . "\" ";
              $femail1 = "value=\"" . $_SESSION["mail"] . "\" ";
          }

          $fnom2 = isset($_POST["nom2"]) ? 'value="' . htmlspecialchars($_POST["nom2"]) . '" ' : "";
          $fprenom2 = isset($_POST["prenom2"]) ? 'value="' . htmlspecialchars($_POST["prenom2"]) . '" ' : "";
          $femail2 = isset($_POST["email2"]) ? 'value="' . htmlspecialchars($_POST["email2"]) . '" ' : "";
          $fnom3 = isset($_POST["nom3"]) ? 'value="' . htmlspecialchars($_POST["nom3"]) . '" ' : "";
          $fprenom3 = isset($_POST["prenom3"]) ? 'value="' . htmlspecialchars($_POST["prenom3"]) . '" ' : "";
          $femail3 = isset($_POST["email3"]) ? 'value="' . htmlspecialchars($_POST["email3"]) . '" ' : "";
          $fcommentaire = isset($_POST["commentaire"]) ? htmlspecialchars($_POST["commentaire"]) : "";


          echo <<<EOF
<h1>Rendre&nbsp;: $titre</h1>

<form action="rendu.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="code" value="$code" />
<p>Étudiant n°1. Nom&nbsp;: <input name="nom1" $fnom1 /> Prénom&nbsp;: <input name="prenom1" $fprenom1 /> Courriel&nbsp;: <input type="email" name="email1" $femail1 /></p>
<p>Étudiant n°2. Nom&nbsp;: <input name="nom2" $fnom2 /> Prénom&nbsp;: <input name="prenom2" $fprenom2 /> Courriel&nbsp;: <input type="email" name="email2" placeholder="Nom.Prenom@supelec.fr" $femail2 /></p>
<p>Étudiant n°3. Nom&nbsp;: <input name="nom3" $fnom3 /> Prénom&nbsp;: <input name="prenom3" $fprenom3 /> Courriel&nbsp;: <input type="email" name="email3" placeholder="Nom.Prenom@supelec.fr" $femail3 /></p>
<p>Commentaire éventuel à transmettre à l'enseignant&nbsp;:</p>
<textarea name="commentaire" cols="60" rows="6">$fcommentaire</textarea>
<div>
<p>Fichiers&nbsp;:</p>
<!-- <input type="hidden" name="MAX_FILE_SIZE" value="5000000"> -->
<ul>
$zoneFichiers
</ul>
</div>
<input type="submit" value="Envoyer" />
</form>

EOF;
        }
    }
}

?>



<?php

foot();

?>
