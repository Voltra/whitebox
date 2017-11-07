<!DOCTYPE html>
<html>
    <head>
    </head>

    <body style="background-color: #e9ebee">
        <h1><?= __FILE__; ?></h1>
        <hr/>
        <p>
            This is<br/>
            <em style="color: #3852FF"><?= $vd("user"); ?></em>'s page,<br/>
            this is using <em style="color: #42ee75">php#<?= $vd("phpversion"); ?></em><br/>

            <ul>
                <?php for($i = 0 ; $i < 3 ; $i+=1): ?>
                    <li>This is #<?= $i; ?></li>
                <?php endfor; ?>
            </ul>
        </p>
    </body>
</html>