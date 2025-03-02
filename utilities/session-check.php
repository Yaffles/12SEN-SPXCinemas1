<?php
/**
 * SESSION CHECK module
 *
 * This module firstly
 * - checks if there has been no activity for the last 30 minute
 * - redirects to login (which logs out and resets the session)
 *
 * Then checks if there is a User object (serialized) in SESSION storage
 * - If not then redirects to login
 */
session_start();
/**
*  for a 30 minute timeout, specified in seconds
*/

$time = $_SERVER['REQUEST_TIME'];
$timeout_duration = 1800;
/**
* Here we look for the user's LAST_ACTIVITY timestamp. If
* it's set and indicates our $timeout_duration has passed,
* blow away any previous $_SESSION data and start a new one.
*/
$last = 0;
$duration = 0;
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $last = $_SESSION['LAST_ACTIVITY'];
    $duration = $time - $last;
    if ($duration > $timeout_duration)  {
        header("Location: login.php");
        exit;
    }
}
/**
* Finally, update LAST_ACTIVITY so that our timeout
* is based on it and not the user's login time.
*/
$_SESSION['LAST_ACTIVITY'] = $time;

/**
 * Check if a Member Object exists - if not redirect to login page
 */
if (!isset($_SESSION["member"])) {  // If a Member Object does not exist?
    header("Location: login.php");  // Redirect to the Login Page
    exit;
}
?>