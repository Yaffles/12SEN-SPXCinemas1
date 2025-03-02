    <footer>
        <div>
        <?php
            require_once('utilities/sanitize.php');
            if (isset($_SESSION['footer'])) {Sanitize::safeEcho($_SESSION['footer']);}
            else {echo("Current Member: Not Logged In - (c) SPX Cinemas 2025");}
        ?>
        </div>
    </footer>