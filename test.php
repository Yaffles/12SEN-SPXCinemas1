<?php

// Test code to ensure encryption works

require_once('utilities/cipher.php');

$msg = "Hello World";

$encrypted_msg = Cipher::encrypt($msg);
echo "$encrypted_msg<br><br>";
echo "Length: ".strlen($encrypted_msg)."<br><br>";

$decrypted_msg = Cipher::decrypt($encrypted_msg);
echo "$decrypted_msg<br><br>";

echo time()."<br>";
echo date("c")."<br><br>";

// Test password hashing

$pwd = "password";
echo $pwd."<br><br>";

$hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);
echo $hashed_pwd."<br>";
echo strlen($hashed_pwd)."<br><br>";

$temp_pwd = "password";
echo $temp_pwd."<br>";
if (password_verify($temp_pwd, $hashed_pwd)) {
    echo "Password verified<br><br>";
} else {
    echo "Password not verified<br><br>";
}

// Test database class

require_once('model/database.php');

$db = new Database();

$sql = "SELECT * FROM members";
$result = mysqli_fetch_all($db->run($sql), MYSQLI_ASSOC);

for ($i=0; $i<count($result); $i++) {
    echo $result[$i]['username']."  ";
    echo $result[$i]['password']."  ";
    echo $result[$i]['firstName']."  ";
    echo $result[$i]['lastName']."  ";
    echo $result[$i]['role']."  ";
    echo $result[$i]['street']."  ";
    echo $result[$i]['town']."  ";
    echo $result[$i]['state']."  ";
    echo $result[$i]['postcode']."  ";
    echo $result[$i]['phone']."  ";
    echo $result[$i]['email']."  <br><br>";
}

$sql = "INSERT INTO members (username, password, firstName, lastName) VALUES ('synetheticJoe', 'dontfindme', 'Joe', 'Man')";

try {
    $db->run($sql);
    echo "check ur database lol";
} catch (Exception $e) {
    echo "Error: ".$e->getMessage()."<br><br>";
}

// Test AuditLog logging functionality

require_once('model/audit-log.php');

$entity = "Users";
$member_id = 5;
$action = "Login";
$entry = "Testing logging functionality, successful";

$log = new AuditLog($entity, $member_id, $action, $entry);

$log->log();

// Test sanitization

require_once("utilities/sanitize.php");

$msg = "<> This is $%!@# a tester ?/<>";
echo $msg."<br><br>";

$msg2 = Sanitize::toHTMLChars($msg);
echo $msg2."<br><br>";

$msg3 = Sanitize::toHTMLChars($msg2);
echo $msg3."<br><br>";

?>