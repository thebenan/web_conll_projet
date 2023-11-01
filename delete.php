<?php
if(isset($_POST['fichier'])) {
    $fichier = $_POST['fichier'];
    if(file_exists("uploads/" . $fichier)) {
        unlink("uploads/" . $fichier);
        echo "Le fichier $fichier a été supprimé avec succès.";
    } else {
        echo "Le fichier $fichier n'a pas été trouvé.";
    }
} else {
    echo "Le fichier n'a pas été spécifié.";
}
?>
