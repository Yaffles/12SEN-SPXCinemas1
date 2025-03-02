<?php
    session_start();
    require_once("model/member.php");
    $member = unserialize($_SESSION["member"]);
    $retCode = $member->logout();

    if ($retCode==0) {
        header("Location:login.php");
        exit();
    }
?>