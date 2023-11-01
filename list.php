<?php
$files = scandir('uploads/');
$files = array_diff($files, array('.', '..'));

foreach($files as $file) {
    echo '<a href="uploads/'.$file.'">'.$file.'</a><br/>';
}
?>
