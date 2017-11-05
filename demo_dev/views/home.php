<?php
//Can alias the view's datasource
$data = $vd;
?>

<!DOCTYPE html>
<html lang="fr-FR">
    <head>
    </head>

    <body>
        <h1><?= __FILE__; ?></h1>
        <hr/>
        <p>This is a homepage made via the native PHP pseudo-rendering system on PHP#<?= $data("version") ?>.</p>
    </body>
</html>
