<!DOCTYPE html>
<html>
<head>
    <title>Application de gestion des fichiers CoNLL-U</title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="style.css" />
    <script type="text/javascript" src="jquery-3.6.1.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="FileSaver.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css" />
</head>



<body>

    <h1>Application web CoNLL-U</h1>
  
    <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Ajouter" name="submit">
    </form>
    <div id="status"></div>
    <div id="fileList">
        Liste des fichiers <select id="liste">
            <option value="" selected disabled>Sélectionnez un fichier</option>
            <?php
            if (is_dir("uploads")) {
                $doss = opendir("uploads");
                while ( $fichier = readdir($doss) ) {
                    if (is_file("uploads/".$fichier)) {
                        print "<option>".$fichier."</option>";
                    }
                }
            } else {
                print "le dossier 'uploads' n'existe pas";
            }
            ?>
        </select>
    </div>

    <div id="visualisation">
        <button id="visualiser">Visualiser</button>
        <p> (Le bouton Visualiser ouvre les fichiers par défaut au format texte brut) </p>
        <div id="viewOptions">
            <button id="textBrut">Texte brut</button>
            <button id="tableau">Tableau</button>
            <button id="texteColore">Texte coloré</button>
        </div>
        <div id="fileContent" style="border: 1px solid black; height: 300px; overflow: auto;"></div>
    </div>


    <div id="dlt">
        <button id = "supprimer"> Supprimer </button>
    </div>
    <div id="export">
        <button id="exportBtn">Exporter</button>
     </div>






</body>

<script>
    $(document).ready(function() {
        // fonction pour afficher la liste des fichiers dans le dossier uploads
        function getFiles() {
            $.ajax({
                url: 'list.php', // URL du script PHP pour récupérer la liste des fichiers
                type: 'GET',
                success: function(response) {
                    var fileList = $('<select>');
                    fileList.append($('<option>').attr('value', '').prop('disabled', true).prop('selected', true).text('Sélectionnez un fichier').fadeIn(2000).delay(3000).fadeOut(2000));
                    $.each(response, function(index, file) {
                        fileList.append($('<option>').text(file));
                    });
                    // ajout de la liste des fichiers à l'élément #fileList
                    $('#fileList').html(fileList);
                },
                error: function(xhr, status, error) {
                    $('#status').empty().append("Erreur lors de la récupération des fichiers : " + error).fadeIn(2000).delay(3000).fadeOut(2000);
                }
            });
        }

        // appel à la fonction getFiles() lors du chargement de la page
        getFiles();

        $('#uploadForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: 'upload.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#status').empty().append(response);
                    // ajout manuel du nouveau fichier à la liste déroulante
                    // au lieu de getFiles(); pour essayer d'afficher la liste actuelle des fichiers après l'ajout
                    //var newFile = $('<option>').text(basename(formData.get('fileToUpload').name));
                    //$('#liste').append(newFile);
                    getFiles();
                },
                error: function(xhr, status, error) {
                    $('#status').empty().append("Erreur lors de l'ajout : " + error).fadeIn(2000).delay(3000).fadeOut(2000);
                }
            });
        });



        $('#supprimer').click(function() {
            var fichier = $('#liste').val();
            console.log("fichier sélectionné : ", fichier); // pour déboguer
            if (fichier) {
                $.ajax({
                    url: 'delete.php',
                    type: 'POST',
                    data: {fichier: fichier},
                    success: function(response) {
                        $('#status').empty().append(response);
                        getFiles();
                    },
                    error: function(xhr,status, error) {
                        $('#status').empty().append("Erreur lors de la suppresion du fichier"+ error).fadeIn(2000).delay(3000).fadeOut(2000);
                    }
                });
            } else {
                $('#status').empty().append("Veuillez sélectionner un fichier à supprimer").fadeIn(2000).delay(3000).fadeOut(2000);
            }
        });


        $('#textBrut, #visualiser').click(function() {
            var fichier = $('#liste').val();
            $.ajax({
                url: 'uploads/' + fichier,
                type: 'GET',
                dataType: 'text',
                success: function(response) {
                    $('#fileContent').html('<pre>' + response + '</pre>');
                },
                error: function(xhr, status, error) {
                    $('#status').empty().append("Erreur lors de l'ouverture du fichier : " + error).fadeIn(2000).delay(3000).fadeOut(2000);
                }
            });
        });

        $('#tableau').click(function() {
            var fichier = $('#liste').val();
            $.ajax({
                url: 'uploads/' + fichier,
                type: 'GET',
                dataType: 'text',
                success: function(response) {
                    var table = $('<table>');
                    // ajout de la première ligne pour représenter les colonnes
                    table.append($('<tr>').append(
                        $('<th>').text('ID'),
                        $('<th>').text('FORM'),
                        $('<th>').text('LEMMA'),
                        $('<th>').text('UPOS'),
                        $('<th>').text('XPOS'),
                        $('<th>').text('FEATS'),
                        $('<th>').text('HEAD'),
                        $('<th>').text('DEPREL'),
                        $('<th>').text('DEPS'),
                        $('<th>').text('MISC')
                    ));
                    // boucle pour ajouter chaque ligne du fichier à la table
                    $.each(response.trim().split('\n'), function(index, line) {
                        // on ignore les lignes vides
                        if (line.trim() != '') {
                            var fields = line.trim().split('\t');
                            table.append($('<tr>').append(
                                $('<td>').text(fields[0]),
                                $('<td>').text(fields[1]),
                                $('<td>').text(fields[2]),
                                $('<td>').text(fields[3]),
                                $('<td>').text(fields[4]),
                                $('<td>').text(fields[5]),
                                $('<td>').text(fields[6]),
                                $('<td>').text(fields[7]),
                                $('<td>').text(fields[8]),
                                $('<td>').text(fields[9])
                            ));
                        }
                    });
                    $('#fileContent').html(table);
                },
                error: function(xhr, status, error) {
                    $('#status').empty().append("Erreur lors de l'ouverture du fichier : " + error).fadeIn(1000).delay(2000).fadeOut(3000);
                }
            });
        });

        $('#exportBtn').click(function() {
            // récupération du contenu du fichier dans la div fileContent
            var fileContent = $('#fileContent').find('pre').text(); // extraire le texte brut de la balise pre

            // récupération du type de contenu du fichier (text brut / tableau)
            var contentType = 'text';
            if ($('#fileContent').find('table').length) {
                contentType = 'table';
            }

            // affichage d'une boîte de dialogue pour demander le type de fichier à exporter
            var fileType;
            switch (contentType) {
                case 'text':
                    fileType = prompt('Sélectionnez le type de fichier à exporter : \n Tapez txt')
                    if (fileType !== 'txt') {
                        alert('Le type de fichier sélectionné n\'est pas valide.\nSi vous voulez exporter le fichier en csv ou en xml, veuillez d\'abord le visualiser en format tableau.');
                        return;
                    }
                    break;
                case 'table':
                    fileType = prompt('Sélectionnez le type de fichier à exporter : \n Tapez csv ou xml');
                    if (fileType !== 'csv' && fileType !== 'xml') {
                        alert('Le type de fichier sélectionné n\'est pas valide.\nSi vous voulez exporter le fichier en txt, veuillez d\'abord le visualiser en format texte brut.');
                        return;
                    }
                    break;
                default:
                    alert('Le type de contenu du fichier n\'est pas valide.');
                    return;
            }

            // création du contenu du fichier à exporter selon le type sélectionné
            var fileData;
            switch (fileType) {
                case 'txt':
                    fileData = fileContent;
                    break;
                case 'csv':
                    // récupération des données de chaque cellule du tableau
                    var data = [];
                    $('#fileContent').find('tr').each(function() {
                        var row = [];
                        $(this).find('th,td').each(function() {
                            row.push($(this).text());
                        });
                        data.push(row.join(';'));
                    });
                    fileData = data.join('\n');
                    break;
                    case 'xml':
                        var data = [];
                        $('#fileContent').find('tr').each(function() {
                            var row = [];
                            row.push('<ID>' + $(this).find('td').eq(0).text() + '</ID>');
                            row.push('<FORM>' + $(this).find('td').eq(1).text() + '</FORM>');
                            row.push('<LEMMA>' + $(this).find('td').eq(2).text() + '</LEMMA>');
                            row.push('<UPOS>' + $(this).find('td').eq(3).text() + '</UPOS>');
                            row.push('<XPOS>' + $(this).find('td').eq(4).text() + '</XPOS>');
                            row.push('<FEATS>' + $(this).find('td').eq(5).text() + '</FEATS>');
                            row.push('<HEAD>' + $(this).find('td').eq(6).text() + '</HEAD>');
                            row.push('<DEPREL>' + $(this).find('td').eq(7).text() + '</DEPREL>');
                            row.push('<DEPS>' + $(this).find('td').eq(8).text() + '</DEPS>');
                            row.push('<MISC>' + $(this).find('td').eq(9).text() + '</MISC>');
                            data.push('<word>' + row.join('') + '</word>');
                        });
                        var fileData = '<?xml version="1.0" encoding="UTF-8"?>\n<sentences>\n' + data.join('\n') + '\n</sentences>';
                        break;


                default:
                    alert('Le type de fichier sélectionné n\'est pas valide.');
                    return;
            }

            // création du fichier et téléchargement
            var fileName = 'export.' + fileType;
            var file = new Blob([fileData], {type: 'text/plain;charset=utf-8'});
            saveAs(file, fileName);
        });







    });
</script>


</html>