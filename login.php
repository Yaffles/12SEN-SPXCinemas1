<?php
/**
 * First Initialise Sessions by destroying previous content and restart
 */
session_start();
session_destroy();
session_unset();
session_start();

require_once("model/member.php");
require_once("utilities/sanitize.php");

$message = "Please login to this wonderful website";

$method  = $_SERVER["REQUEST_METHOD"];

if ($method == "POST") {

    $user_name = Sanitize::toHTMLChars($_POST["userName"]);
    $password = Sanitize::toHTMLChars($_POST["password"]);
    //echo("Pwd: ".$password);

    $member = new Member();

    $message = "Invalid Login, Try Again";

    $ret_code = $member->login($user_name, $password);

    switch ($ret_code) {
        case 0:
            $message = "Login successfully completed. Welcome ".$member->getFirstName().' '.$member->getLastName();
            //Store Member object into Session
            $_SESSION["member"] = serialize($member);
            $_SESSION["footer"] = "Current Member: ".$member->getUsername()." (".$member->getFirstName()." ".$member->getLastName().") - (c) SPX Cinemas 2025";
            //redirect to home page after login
            header("Location: index.php");
            // exit;
        case 1:
            $message = "Invalid Username. Try again";
        case 2:
            $message = "Invalid Password. Try again";
        case 9:
            $message = "Invalid Login. Try again";
    }

}
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
        <script src="js/toggle-password-visibility.js"></script>
        <h1>Login</h1>
        <div class="border border-dark">
            <form name="login" method="POST" action="">
                <div class="row">
                    <div class="col m-3">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="userName" placeholder="Enter username" name="userName" required>
                    </div>
                    <div class="col m-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required>
                    </div>
                    <div class="col-md-auto">
                        <img id="toggle" onclick="togglePasswordVisibility()" src="img/eye-open.png" width="50vw" height="90vh" style="padding-top: 5vh;">
                    </div>
                </div>

                <div class="form-check m-3">
                    <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" name="remember"> Remember me
                    </label>
                </div>
                <div class="m-3">
                    <button type="submit" class="btn btn-primary m-auto">Submit</button>
                </div>
                <div class="row m-3 message alert-danger">
                    <?php Sanitize::safeEcho($message); ?>
                </div>
            </form>
        </div>
    </maincontent>
    <?php
        require('footer.php');
    ?>
</body>
</html>