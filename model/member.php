<?php

require_once("database.php");
require_once("audit-log.php");
require_once(__DIR__."/../utilities/cipher.php");

/**
 * STATIC secured_decrypt().
 * Two stage decryption of data
 *
 * Relies of config.php file to declare FIRSTKEY and SECONDKEY constants
 * see: https://www.php.net/manual/en/public function.openssl-encrypt.php
 */

class Member extends Database {

    private $member_id;
    private $user_name;
    private $password;
    private $first_name;
    private $last_name;
    private $role;
    private $street;
    private $town;
    private $state;
    private $postcode;
    private $phone;
    private $email;

    private $table_name;
    // private $audit_log; // Get an auditlog to write to database

    /**
     * Constructor.
     *
     * All the fields in user default to null if not provided.
     */
    public function __construct(
        $member_id=null,
        $user_name=null,
        $password=null,
        $first_name=null,
        $last_name=null,
        $role="User",
        $street=null,
        $town=null,
        $state=null,
        $postcode=null,
        $phone=null,
        $email=null
    ) {
        parent::__construct(); // gets a database connection
        $this->table_name = "members";

        $this->setMemberId($member_id);
        $this->setUserName($user_name);
        $this->setPassword($password);
        $this->setFirstName($first_name);
        $this->setLastName($last_name);
        $this->setRole($role);
        $this->setStreet($street);
        $this->setTown($town);
        $this->setState($state);
        $this->setPostcode($postcode);
        $this->setPhone($phone);
        $this->setEmail($email);
        // IF ($this->userExists()) {
        //     // Use this bit to get Aggregations
        // }
    }
    public function __destruct() {
        //echo("Destroying Member object");
    }

    public function setMemberId($member_id) {
        if ($member_id) {
            $this->member_id = $member_id;
        }
    }
    public function setUserName($user_name) {
        if ($user_name) {
            $this->user_name = $user_name;
        }
    }
    public function setPassword($password) {
        if ($password) {
            $this->password = $password;
        }
    }
    public function setFirstName($first_name=null) {
        if ($first_name) {
            $this->first_name = $first_name;
        }
    }
    public function setLastName($last_name=null) {
        if ($last_name) {
            $this->last_name = $last_name;
        }
    }
    public function setRole($role=null) {
        if ($role) {
            $this->role = $role;
        }
    }
    public function setStreet($street=null) {
        if ($street) {
            $this->street = $street;
        }
    }
    public function setTown($town=null) {
        if ($town) {
            $this->town = $town;
        }
    }
    public function setState($state=null) {
        if ($state) {
            $this->state = $state;
        }
    }
    public function setPostcode($postcode=null) {
        if ($postcode) {
            $this->postcode = $postcode;
        }
    }
    public function setPhone($phone=null) {
        if ($phone) {
            $this->phone = $phone;
        }
    }
    public function setEmail($email=null) {
        if ($email) {
            $this->email = $email;
        }
    }

    public function getMemberId() {
        return $this->member_id;
    }

    public function getUserName() {
        return ($this->user_name);
    }
    public function getFirstName() {
        return ($this->first_name);
    }
    public function getLastName() {
        return ($this->last_name);
    }
    public function getFullName() {
        return ($this->first_name)." ".($this->last_name);
        ;
    }
    public function getRole() {
        return ($this->role);
    }
    public function getStreet() {
        return ($this->street);
    }
    public function getTown() {
        return ($this->town);
    }
    public function getState() {
        return ($this->state);
    }
    public function getPostcode() {
        return ($this->postcode);
    }
    public function getPhone() {
        return ($this->phone);
    }
    public function getEmail() {
        return ($this->email);
    }

    public function log($action, $entry, $entity="Users") {
        // echo("<script>console.log('Entity:".$entity.", Action:".$action.", Entry:".$entry."');</script>");
        $audit = new AuditLog($entity, $this->getMemberId(), $action, $entry);
        $audit->log();
    }

    /**
     * Method  userExists
     * @param  $user_name  optional
     *
     * Determines if user exists in database - returns TRUE or FALSE
     */
    public function userExists($user_name=null) {

        $action = "Check";

        if ($user_name) {
            $this->setUserName($user_name);
        }
        // echo("Check: $userName");
        $sql        = "SELECT COUNT(*) AS numRows FROM ".$this->table_name." WHERE userName = '".$this->getUserName()."'";
        $result     = $this->run($sql);
        $num_rows   = $result->fetch_assoc()['numRows']; //num_rows;
        //echo($numRows);
        if ($num_rows == 1) {
            $entry = "UserName: <".$this->getUserName()."> - User exists";
            $this->log($action, $entry);
        } else {
            $entry = "UserName: <".$this->getUserName()."> - User does not exist";
            $this->log($action, $entry);
        }

    return ($num_rows == 1);
    }

    /**
     * Method login
     * 
     * This method takes the username and password and verifies
     * 
     * Returns different return codes to flag success or failures
     * 
     * Return code meaning:
     * 
     *         0    Success
     *         1    Invalid Username
     *         2    Invalid Password
     *         9    Generic Error
     *
     * @param $i_user_name   Input User Name
     * @param $i_password    Input Password
     */

    public function login($i_user_name=null, $i_password=null) {

        // echo("Login with {".$iUserName."}, {".$iPassword."}<br/>");

        $ret_code = 9;
        $action = "Login";

        $sql = "SELECT u.memberId, u.userName, u.firstName, u.lastName, u.password, u.role, u.street, u.town, u.state, u.postcode, u.phone, u.email FROM ".$this->table_name." AS u WHERE u.userName = ?";

        try {
            //NOTE: This is too complex for a generic Database Class Function
            $stmt = $this->getConn()->prepare($sql);
            $stmt->bind_param('s', $i_user_name);
            //echo($sql);

            //Executing the statement
            $stmt->execute();
            /* Store the result (to get properties) */
            $stmt->store_result();

            //Binding values in result to variables - note these are encrypted values
            // Note: this does not fetch the data, just maps the db columns to the fields
            $first_name=null;
            $last_name=null;
            $temp_password=null;
            $role=null;
            $street=null;
            $town=null;
            $state=null;
            $postcode=null;
            $phone=null;
            $email=null;

            $stmt->bind_result(
                $this->member_id,
                $this->user_name,
                $first_name,
                $last_name,
                //$this->password, // better not to store this
                $temp_password,
                $role,
                $street,
                $town,
                $state,
                $postcode,
                $phone,
                $email
            );
            /* Get the number of rows */
            $num_of_rows = $stmt->num_rows;

            if ($num_of_rows <= 0) {
                $ret_code = 1;
                $entry = "UserName: <".$i_user_name."> - Failed login: invalid UserName";
                $this->log($action, $entry);
            } else {
                // Now fetch the result data from stmt object into the bound variables
                $stmt->fetch();
                //Verify the password before continuing
                if (password_verify($i_password, $temp_password)) {
                    $ret_code = 0;
                    $entry = "UserName: <".$this->getUserName()."> - Login successful";
                    $this->log($action, $entry);

                    // Decrypt the fields
                    $this->first_name = Cipher::decrypt($first_name);
                    $this->last_name = Cipher::decrypt($last_name);
                    $this->role = Cipher::decrypt($role);
                    $this->street = Cipher::decrypt($street);
                    $this->town = Cipher::decrypt($town);
                    $this->state = Cipher::decrypt($state);
                    $this->postcode = Cipher::decrypt($postcode);
                    $this->phone = Cipher::decrypt($phone);
                    $this->email = Cipher::decrypt($email);
                } else {
                    $ret_code = 2;
                    $entry = "UserName: <".$this->getUserName()."> - Failed login: invalid Password";
                    $this->log($action, $entry);
                }
            }
            $stmt->close();

        } catch (Exception $e) {
            // echo("Error: ".$e->getMessage());
            $ret_code = 9;
            $entry = "UserName: <".$i_user_name."> - Failed login: ERROR >>> ".$e->getMessage();
            $this->log($action, $entry);
        }
        return $ret_code;
    }

    /**
     * Method logout
     *
     * Records logout and returns 0 to signify success
     */

    public function logout() {
        $ret_code = 0;
        $action = "Logout";

        $entry = "UserName: <".$this->getUserName()."> - Has logged out";
        $this->log($action, $entry);

        return $ret_code;
    }

    /**
     * Method save
     *
     * If $memberId exists - then UPDATE record otherwise INSERT new record
     *
     * Note: we use the fields directly rather that the get method - as we want the encrypted value
     * 
     * Return code meaning:
     * 
     *          0    Success
     *          1    Update Failed
     *          2    Add Failed
     *          9    Generic Error
     */

    public function save() {
        $ret_code = 9;
        $action = "Save";

        if ($this->member_id) {  //Existing Record
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
            $first_name = Cipher::encrypt($this->first_name);
            $last_name = Cipher::encrypt($this->last_name);
            $street = Cipher::encrypt($this->street);
            $town = Cipher::encrypt($this->town);
            $state = Cipher::encrypt($this->state);
            $postcode = Cipher::encrypt($this->postcode);
            $phone = Cipher::encrypt($this->phone);
            $email = Cipher::encrypt($this->email);

            $sql = <<<EOD
                UPDATE $this->table_name SET
                    userName = '$this->user_name',
                    password = '$password_hash',
                    firstName = '$first_name',
                    lastName = '$last_name',
                    street = '$street',
                    town = '$town',
                    state = '$state',
                    postcode = '$postcode',
                    phone = '$phone',
                    email = '$email'
                WHERE memberId = $this->member_id
                EOD;
            // ECHO($sql."<br/>");

            if ($this->run($sql)) {
                $entry = "UserName: <".$this->getUserName()."> - Update successful";
                $this->log($action, $entry);
                $this->commit();
                $ret_code = 0;
            } else {
                $entry = "UserName: <".$this->getUserName()."> - Update failed: SQL >>> '".$sql."', ERROR >>> ".$this->getError();
                $this->log($action, $entry);
                $ret_code = 1;
            }

        } else {  // New Record
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
            $first_name = Cipher::encrypt($this->first_name);
            $last_name = Cipher::encrypt($this->last_name);
            $street = Cipher::encrypt($this->street);
            $town = Cipher::encrypt($this->town);
            $state = Cipher::encrypt($this->state);
            $postcode = Cipher::encrypt($this->postcode);
            $phone = Cipher::encrypt($this->phone);
            $email = Cipher::encrypt($this->email);

            // Using Heredoc to allow multiline strings - may or maynot work
            $sql = <<<EOD
                INSERT INTO $this->table_name
                (role, userName, password, firstName, lastName, street, town, state, postcode, phone, email)
                VALUES (
                    '$this->role',
                    '$this->user_name',
                    '$password_hash',
                    '$first_name',
                    '$last_name',
                    '$street',
                    '$town',
                    '$state',
                    '$postcode',
                    '$phone',
                    '$email'
                )
                EOD;

            if ($this->run($sql)) {
                // Once INSERT is done, retrieve the new memberId and store in User object
                $this->member_id = $this->getConn()->insert_id;

                $entry = "UserName: <".$this->user_name."> - Add successful";
                $this->log($action, $entry);
                $this->commit();
                $ret_code = 0;
            } else {
                $entry = "UserName: <".$this->user_name."> - Failed Add: SQL >>> '".$sql."', ERROR >>> ".$this->getError();
                // ECHO("User Save Add Error: " . $sql . "<br>" . $this-getError());
                $this->log($action, $entry);
                $ret_code = 2;
            }

        }
        return $ret_code;

    }

    /**
     * Method: Delete
     *
     * If $memberId exists - then DELETE record
     * 
     * Return code meaning:
     * 
     *          0    Success
     *          2    Delete Failed
     *          9    Generic Error
     */

    public function delete() {

        $sql = "";
        // Comment out until DB is created
        //$sql .= "DELETE FROM basketItems WHERE basketId = (SELECT basketId FROM baskets WHERE memberId = $memberId); DELETE FROM baskets WHERE memberId = $memberId; DELETE FROM orderItems WHERE orderId = (SELECT orderId FROM orders WHERE memberId = $memberId); DELETE FROM orders WHERE memberId = $memberId;"
        $sql .= "DELETE FROM ".$this->table_name." WHERE memberId=".$this->getMemberId();
        $ret_code = 9;
        $action = "Delete";

        if ($this->runMulti($sql) === TRUE) {
            $entry = "UserName: <".$this->user_name."> - Delete successful";
            $this->log($action, $entry);
            $this->commit();            # Once User is deleted ,
            # then force User to login again or create new User
            $_SESSION["user"]=null;
            $ret_code = 0;
        } else {
            $entry = "UserName: <".$this->user_name."> - Failed delete: SQL >>> '".$sql."', ERROR >>> ".$this->getError();
            $this->log($action, $entry);
        }
    // } ELSE IF ($action == "ord") {
    //     header("Location: UserOrders.php");
    // } ELSE {
    //     $message = "Error: Invalid Action attempted: ".$action;
    //     $this->auditLog->addLog(entity:"User",action:"DELETE",entry:"Delete Failed:  memberId:".$this->memberId.", UserName:".$this->userName.", sql=".$sql);
    //     $retCode = 9;

        return $ret_code;
    }

}