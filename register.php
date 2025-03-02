<?php
require_once("model/member.php");
require_once("utilities/sanitize.php");

// First check IF we are already logged in - get session data with the session_start
session_start();
// IF UserName exists then already logged in - so we are updating Member details

$user_id = 0;
$user_name   = "";
$password   = "";
$password_2  = "";
$first_name  = "";
$last_name   = "";
$street     = "";
$town       = "";
$state      = "";
$postcode   = "";
$phone      = "";
$email      = "";
$action     = "";

// IF already Logged In, then UPDATE mode - display existing Member details and allow updates/delete
if (isset($_SESSION["member"])) {
    $member = unserialize($_SESSION["member"]);  //get the stored Member object

    $mode = "UPD";
    // $userName = $_SESSION["userName"];
    // echo("member:".$userName);
    $message = "Please Amend or Delete your Member details";
    // Otherwise IF not logged in - we come here in ADD mode.
} else {
    $mode = "ADD";
    $message = "Please Register for this wonderful website";
    $member = new Member();
}
// echo("Mode:".$mode);

$method  = $_SERVER["REQUEST_METHOD"];

// POST means we have pressed a SUBMIT button.
if ($method == "POST") {
    $user_name  = $_POST["userName"];
    $password   = $_POST["password"];
    $password_2 = $_POST["password2"];
    $first_name = $_POST["firstName"];
    $last_name  = $_POST["lastName"];
    $street     = $_POST["street"];
    $town       = $_POST["town"];
    $state      = $_POST["state"];
    $postcode   = $_POST["postcode"];
    $phone      = $_POST["phone"];
    $email      = $_POST["email"];
    
    if ($mode == "ADD") {
        $message = addNew($member);
    } else {
        $message = update($member);
    }
} // Finish POST

// //GET mode - this is the first time through.
// // IF update mode - get the Member record based on the UserName

if ($mode == "UPD") {
    $user_id    = $member->getMemberId();
    $user_name  = $member->getUserName();
    $password   = "";
    $password_2 = "";
    $first_name = $member->getFirstName();
    $last_name  = $member->getLastName();
    $street     = $member->getStreet();
    $town       = $member->getTown();
    $state      = $member->getState();
    $postcode   = $member->getPostcode();
    $phone      = $member->getPhone();
    $email      = $member->getEmail();
}

/**
 * Add New Member
 *
 * Passed in an empty Member object
 * Get fields from the screen
 * Populate the Member Object
 * Save Member to database
 * Force Member to Login again
 *
 * @param $member empty instanceof Member class (see Member.php)
 */

function addNew($member) {

    $password   = Sanitize::toHTMLChars($_POST["password"]);
    $password2  = Sanitize::toHTMLChars($_POST["password2"]);
    $message = "Adding new Member: (".$member->getMemberId().") ".$member->getUserName()."-".$member->getFullName();

    if ($password != $password2) {
        $message = "Add Member Error: Passwords do not match. Try again!";
    } else {
        $member->setUserName(Sanitize::toHTMLChars($_POST["userName"]));
        $member->setPassword(Sanitize::toHTMLChars($_POST["password"]));
        $member->setFirstName(Sanitize::toHTMLChars($_POST["firstName"]));
        $member->setLastName(Sanitize::toHTMLChars($_POST["lastName"]));
        $member->setStreet(Sanitize::toHTMLChars($_POST["street"]));
        $member->setTown(Sanitize::toHTMLChars($_POST["town"]));
        $member->setState(Sanitize::toHTMLChars($_POST["state"]));
        $member->setPostcode(Sanitize::toHTMLChars($_POST["postcode"]));
        $member->setPhone(Sanitize::toHTMLChars($_POST["phone"]));
        $member->setEmail(Sanitize::toHTMLChars($_POST["email"]));

        $action = $_POST["btnAction"];  #which button was pressed?

        if ($member->userExists()) {
        // echo("member: ".$userName." exists");
            $message = "Add Member: UserName (".$member->getUserName().") already exists! Please choose another";
        } else {
        // Check that password is entered the same twice as a Verification

            # Now add the new Member
            // # Hash Password - see https://www.php.net/manual/en/faq.passwords.php
            // $password = password_hash($password, PASSWORD_DEFAULT);

            if ($action == "add") {
                // $sql = "INSERT INTO Members (userName, password, firstName, lastName, street, town, state, postcode, phone, email) VALUES ('$userName', '$password', '$firstName', '$lastName', '$street', '$town', '$state', '$postcode','$phone','$email')";

                $ret_code = $member->save();
                if ($ret_code == 0) {
                    $message = "New record created successfully";
                    // Force the Member to login again after adding their Member details
                    header("Location: login.php");
                } else {
                    $message = "Error: " . $member->getConn()->error;
                    //echo($message);
                }
            }
        }
    }
    return $message;
}

function update($member) {

    $action = $_POST["btnAction"];
    // Check that password is entered the same twice as a Verification
    $password    = Sanitize::toHTMLChars($_POST["password"]);
    $password_2  = Sanitize::toHTMLChars($_POST["password2"]);
    // $user_id    = $_POST["userId"];
    // echo("update  memberId=".$userId."<br/>");

    // // Check IF Member Order History requested - IF so navigate to Member Orders
    // IF ($action == "ord") {
    //     header("Location: MemberOrders.php");
    // }

    # for update and delete - passwords match
    if ($password != $password_2) {
        $message = "Passwords do not match. Try again!";
    } else {
        // $user_name   = $_POST["userName"];

        if ($action == "upd") {
            $member->setUserName(Sanitize::toHTMLChars($_POST["userName"]));
            $member->setPassword(Sanitize::toHTMLChars($_POST["password"]));
            $member->setFirstName(Sanitize::toHTMLChars($_POST["firstName"]));
            $member->setLastName(Sanitize::toHTMLChars($_POST["lastName"]));
            $member->setStreet(Sanitize::toHTMLChars($_POST["street"]));
            $member->setTown(Sanitize::toHTMLChars($_POST["town"]));
            $member->setState(Sanitize::toHTMLChars($_POST["state"]));
            $member->setPostcode(Sanitize::toHTMLChars($_POST["postcode"]));
            $member->setPhone(Sanitize::toHTMLChars($_POST["phone"]));
            $member->setEmail(Sanitize::toHTMLChars($_POST["email"]));

            $ret_code = $member->save();
            if ($ret_code == 0) {
                $message = "Member record updated successfully";
                //header("Location: MemberRegistration.php");
            } else {
                $message = "Error: " . $member->getConn()->error;
                Sanitize::toHTMLChars($message);
            }
        } else if ($action == "del") {
            // Check IF Member has any basket/orders and DELETE them first ...maybe CASCADE delete them FIRST
            $conn = $member->getConn();
            $ret_code = $member->delete();
            $_SESSION["member"]=null;
            $member = null;
            if ($ret_code == 0) {
                $message = "Member record deleted successfully";
                header("Location: register.php");
            } else {
                $message = "Error: " . $conn->error;
                Sanitize::toHTMLChars($message);
            }
        }
    }
    return $message;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php');?>
    </head>
    <body>
         <?php require('header.php'); ?>
         <?php require('nav.php'); ?>
         <maincontent>
            <script src="js/toggle-password-visibility.js"></script>
            <h1>Member Registration</h1>
            <div class="container border border-dark">
                <form class="" name="register" action="" method="POST">
                    <div class="row mt-3">
                        <div class="col">
                        <label for "userName" class="form-label">Username: </label>
                        <input class="form-control" name="userName" type="text" size="10" maxlength="10" value="<?php Sanitize::safeEcho($user_name);?>"></input>
                        <input type="hidden" name="userId" type="text" value="<?php Sanitize::safeEcho($user_id);?>"></input>
                        </div>
                        <div class="col">
                        <label for "password" class="form-label">Password: </label>
                        <input class="form-control" id="password" name="password" type="password" size="12" maxlength="12" value="<?php Sanitize::safeEcho($password); ?>" required></input>
                        </div>
                        <div class="col-md-auto">
                        <img id="toggle" onclick="togglePasswordVisibility()" src="img/eye-open.png" width="50vw" height="75vh" style="padding-top: 3vh;">
                        </div>
                        <div class="col">
                        <label for "password2" class="form-label">Repeat Password: </label>
                        <input class="form-control" name="password2" type="password" size="12" maxlength="12" value="<?php Sanitize::safeEcho($password_2); ?>" required></input>
                        </div>
                    </div>
                    <div class="row  mt-5">
                        <div class="col">
                        <label for "firstName" class="form-label">First Name: </label>
                        <input class="form-control" name="firstName" type="text" size="35" maxlength="35" value="<?php Sanitize::safeEcho($first_name); ?>" ></input>
                        </div>
                        <div class="col">
                        <label for "lastName" class="form-label">Last Name: </label>
                        <input class="form-control" name="lastName" type="text" size="35" maxlength="35" value="<?php Sanitize::safeEcho($last_name); ?>"></input>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <label for "street" class="form-label">Street: </label>
                            <input class="form-control" name="street" type="text" size="50" maxlength="50" value="<?php Sanitize::safeEcho($street); ?>"></input>
                        </div>
                        <div class="col-4">
                            <label for "town" class="form-label">Town: </label>
                            <input class="form-control" name="town" type="text" size="50" maxlength="50" value="<?php Sanitize::safeEcho($town); ?>"></input>
                        </div>
                        <div class="col-1">
                            <label for "state" class="form-label">State: </label>
                            <input class="form-control" name="state" type="text" size="3" maxlength="3" value="<?php Sanitize::safeEcho($state); ?>"></input>
                        </div>
                        <div class="col-1">
                            <label for "postcode" class="form-label">Postcode: </label>
                            <input class="form-control" name="postcode" type="text" size="4" maxlength="4" pattern="[0-9]{4}" value="<?php Sanitize::safeEcho($postcode); ?>"></input>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2">
                            <label for "phone" class="form-label">Phone: </label>
                            <input class="form-control" name="phone" type="text" size="4" value="<?php Sanitize::safeEcho($phone); ?>"></input>
                        </div>
                        <div class="col-3">
                            <label for "email" class="form-label">Email: </label>
                            <input class="form-control" name="email" type="email" size="50" maxlength="50" value="<?php Sanitize::safeEcho($email); ?>"></input>
                        </div>
                    </div>


                    <div class="mb-3 mt-3 row">
<?php               if ($mode=="ADD") {   ?>
                        <div class="col-2">
                            <button type="submit" name="btnAction" value="add" class="btn btn-primary">Add New Member</button>
                        </div>
<?php               } else {
?>
                        <div class="col-2">
                            <button type="submit" name="btnAction" value="upd" class="btn btn-primary">Update Member</button>
                        </div>
                        <div class="col-2">
                            <button type="submit" name="btnAction" value="del" class="btn btn-primary">Delete Member</button>
                        </div>
                        <!-- <div class="col-4">
                            <button type="submit" name="btnAction" value="ord" class="btn btn-success">Member Order History</button>
                        </div> -->
<?php               }   ?>
                    </div>
                    <div class="row mx-auto mb-3 mt-3 message alert-danger">
                        <?php Sanitize::safeEcho($message); ?>
                    </div>
                </form>
            </div>

         </maincontent>
         <?php require('footer.php'); ?>
    </body>
</html>