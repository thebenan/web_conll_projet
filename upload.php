<?php
if(isset($_FILES['fileToUpload'])) {
  $targetDir = "uploads/";
  $targetFile = $targetDir . basename($_FILES['fileToUpload']['name']);
  if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFile)) {
    echo "Le fichier ". basename($_FILES['fileToUpload']['name']). " a été ajouté avec succès.";
  } else {
    echo "Une erreur est survenue lors de l'ajout du fichier.";
  }
}
?>
