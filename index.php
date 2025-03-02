<?php
require(__DIR__.'\utilities\session-check.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require('head.php');?>
</head>
<body>
    <?php
        require('header.php');
        require('nav.php');
    ?>
    <maincontent>
        <h1>Welcome to SPX Cinemas</h1>
        <div >
            <img class="main-img" src="img/cover-image.jpeg"></img>
        </div>
        <br>
        <div>
            <p>Whatever cinema you fancy, we've got it! This Thursday tickets running out, so book now before its too late! You don't want to miss the new release, <b>Cats... PLUS Dogs!</b><b></b></p>
        </div>
    </maincontent>
    <?php
        require('footer.php');
    ?>
</body>
</html>