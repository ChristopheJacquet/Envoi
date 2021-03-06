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

$email_check = array(1 => "ok", 2=> "unknown", 3=> "unknown");

//    print_r($_POST);

if(empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    echo "<p>Fichier trop gros vis-à-vis des réglages du serveur. Merci de demander à votre encadrant d'augmenter post_max_size.</p>";
} elseif(!isset($_POST["code"])) {
    echo "<p>Erreur, un code de livraison doit être donné. <a href=\"index.php\">Retour à l'accueil</a>.</p>";
} else {
    // un code existe, mais le formulaire reste à remplir
    $code = $_POST["code"];

    $res = DB::request("SELECT idRendu, titre, notification FROM rendu WHERE code=?", array($code));
    $all = $res->fetchAll();
    if(count($all) < 1) {
        echo "<p>Mauvais code de livraison. Veuillez vérifier votre saisie. <a href=\"index.php\">Retour à la saisie du code</a>.</p>";
    } else {
        // OK vérifications faites
        $obj = $all[0];
        $titre = $obj->titre;
        $idRendu = $obj->idRendu;
        $notification = $obj->notification;


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
                        
                        /* DEBUG
                        echo "<pre>";
                        echo "Temp: '" . htmlspecialchars($tmpName) . "'   ";
                        echo "%%%% exists=" . file_exists($tmpName);
                        echo "   sz=" . filesize($tmpName) . "/   ";
                        echo "\n";
                        passthru("head {$tmpName} |hexdump -C");
                        echo "\n\n";
                        passthru("file {$tmpName}");
                        echo "///";
                        echo "</pre>";
                        print_r($tmpName);
                         */
                        
                        if(file_exists($tmpName)) {
                            $cmd = escapeshellcmd(Local::$basedir . "/filters/" . $r->script) . " " . escapeshellarg($tmpName);
                            exec($cmd, $output, $retval);
                            if($retval != 0) {
                                if($conformes) echo "<div class=\"error\">\n";
                                echo "<!-- Error code: " . $retval . " -- Comamnd: $cmd -->";
                                
                                if($retval >= 126) {
                                        echo "<p>Erreur probable de configuration de l'outil de livraison. Merci de prévenir votre encadrant et de lui montrer ce message.</p>";
                                        
                                        if($retval == 126) echo "<p>Cause probable : filtre non exécutable ($cmd)</p>";
                                        elseif($retval == 127) echo "<p>Cause probable : filtre non trouvé ($cmd)</p>";
                                        else echo "<p>Cause : erreur $retval.</p>";
                                } else {
                                        echo "<p>Le fichier « {$r->nom} » n'est pas conforme: </p><pre>" . implode("\n", $output) . "</pre>";
                                }
                                $conformes = FALSE;
                            }
                        } else {
                            echo "<div class='error'>Problème lors de l'envoi : fichier non transmis sur le serveur. Merci de contacter votre encadrant. Nom temporaire de fichier : <code>{$tmpName}</code>";
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
            
            // Vérification des adresses e-mail
            for($i = 1; $i<=3; $i++) {
                if(isset($_POST["email" . $i]) && trim($_POST["email" . $i]) != "") {
                    $email = trim($_POST["email" . $i]);
                    $teststr = "E-mail " . htmlspecialchars($email);
                    $infos = array();
                    if(Local::is_valid_email($_SESSION["login"], $email, $infos)) {
                        echo "<!-- $teststr OK (";
                        foreach($infos as $k => $v) {
                            echo htmlspecialchars($k) . ": " . htmlspecialchars($v) . ", ";
                        }
                        echo ") -->\n";
                        $email_check[$i] = "ok";
                    } else {
                        if($conformes) echo "<div class=\"error\">\n";
                        echo "<p>$teststr invalide !</p>";
                        $conformes = FALSE;
                        $email_check[$i] = "nok";
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

                // E-mail de notification (enseignant)
                $notifmail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
                $notifmail->IsSMTP(); // telling the class to use SMTP
                $notifmail->Host       = Local::$smtp_relay; // SMTP server
                $notifmail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
                $notifmail->SetFrom(Local::$from_email, Local::$from_name);
                $notifmail->ClearReplyTos();
                $notifmail->CharSet = "UTF-8";

                // Fin préparation e-mail

                // traitement participants
                for($i = 1; $i<=3; $i++) {
                    if(isset($_POST["nom" . $i]) && trim($_POST["nom" . $i]) != "" && trim($_POST["email" . $i]) != "") {
                        // Ajout à l'e-mail
                        $email = $_POST["email" . $i];
                        echo "<!-- ";
                        try {
                            $mail->AddAddress($email, $email);
                            $notifmail->AddReplyTo($email, trim($_POST["prenom" . $i]) . " " . trim($_POST["nom" . $i]));
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


                $tousParticipants = "";
                // traitement participants
                for($i = 1; $i<=3; $i++) {
                    if(isset($_POST["nom" . $i]) && trim($_POST["nom" . $i]) != "" && trim($_POST["email" . $i]) != "") {
                        $nom = $_POST["nom" . $i];
                        $prenom = $_POST["prenom" . $i];
                        if(strlen($tousParticipants) > 0) $tousParticipants .= ", ";
                        $tousParticipants .= $prenom . " " . $nom;
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

                        

                        $idFichier = substr($formFileId, 7);

                        $req = "INSERT INTO fichierDonne (idRenduDonne, nom, type, idFichier) ".
                            "VALUES (?, ?, ?, ?)";

                        //echo "<!-- $req -->";

                        $idFichierDonne = DB::insert_autoinc($req, 
                                array($idRenduDonne, $fileName, $fileType, $idFichier));

                        fileIdToPath($idFichierDonne, $fspath, $fsname);
                        createFilePath($fspath);
                        
                        
                        // déplace à son emplacement final dans le data store
                        move_uploaded_file($tmpName, $fspath . $fsname);
                        
                        /*
                        $fp      = fopen($tmpName, 'r');
                        $content = fread($fp, filesize($tmpName));
                        #$content = addslashes($content);  No longer needed with PDO
                        fclose($fp);
                         * */

                        // Ajout à l'e-mail
                        $mail->AddAttachment($fspath . $fsname, $fileName);
                        
                        if(true)
                            echo "<p>Fichier $fileName téléchargé</p>";

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
                /* Inutile maintenant qu'on les déplace...
                foreach($_FILES as $formFileId => $file) {
                    if($file["size"] > 0) {
                        unlink($file["tmp_name"]);
                    }
                }
                 * */

                
                echo "<div class='success'><p>Compte-rendu livré avec succès. <a href=\"index.php\">Retour à l'accueil</a>.</p></div>";

                
                // Notification à l'enseignant ?
                if(! is_null($notification)) {
                    echo "<p>Envoi d'une notification à l'enseignant: <!-- ";
                    try {
                        $notifmail->Subject = "Livraison $code : $tousParticipants";
                        $notifmail->Body = "Livraison du travail : " . $obj->titre . "\n\nÉtudiants : " . $tousParticipants . "\n\nCommentaire :\n" . $commentaire;
                        $notifmail->AddAddress($notification, $notification);
                        $notifmail->Send();
                        echo " --> OK.</p>\n";
                    } catch (Exception $ex) {
                        echo " --> Echec. <span style='color:red;'>Veuillez lui signaler.</span></p>\n";
                    }
                }


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

          $placeholder = Local::$email_placeholder;

          echo <<<EOF
<h1>Rendre&nbsp;: $titre</h1>

<form action="rendu.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="code" value="$code" />
<p class="coords_etudiant">Étudiant n°1. Courriel&nbsp;: <input type="email" name="email1" class="coords_email_{$email_check[1]}" size="40" $femail1 /> Nom&nbsp;: <input name="nom1" size="25" $fnom1 /> Prénom&nbsp;: <input name="prenom1" size="25" $fprenom1 /></p>
<p class="coords_etudiant">Étudiant n°2. Courriel&nbsp;: <input type="email" name="email2" class="coords_email_{$email_check[2]}" size="40" autofocus placeholder="$placeholder" $femail2 /> Nom&nbsp;: <input name="nom2" size="25" $fnom2 /> Prénom&nbsp;: <input name="prenom2" size="25" $fprenom2 /></p>
<p class="coords_etudiant">Étudiant n°3. Courriel&nbsp;: <input type="email" name="email3" class="coords_email_{$email_check[3]}" size="40" placeholder="$placeholder" $femail3 /> Nom&nbsp;: <input name="nom3" size="25" $fnom3 /> Prénom&nbsp;: <input name="prenom3" size="25" $fprenom3 /></p>
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
