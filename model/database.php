<?php
//namespace MySQL - useful when different database types ref: new MySQL\DBConnect
/**
 * DBConnect Class.
 * Used to create a persistent connection to the database
 */
//require(__DIR__.'/../utilities/cipher.php');

class Database {

    // Instance Variables

    public $db_user;
    public $db_password;
    public $db_server;
    public $db_name;
    public $conn;
    public $stmt;

    /**
     * Constructor.
     * 
     * Run when a connection instance is created
     **/

    public function __construct($db_server=null,$db_user="12sen_user", $db_password="12sen_user",$db_name="12sen_spxcinemas")
    {
        // echo("New Database<br/>");
        if (!$this->conn) {
            $this->setDbServer($db_server);
            $this->setDbUser($db_user);
            $this->setDbPassword($db_password);
            $this->setDbName($db_name);
            $this->connect();
            //echo("Database: New connection<br/>");
        } else {
            //echo("Database: Existing connection<br/>");
        }
    }

    public function setDbServer($db_server=null) {
        if ($db_server) {
            $this->db_server = $db_server;
        } else {
            $this->db_server = $_SERVER['SERVER_NAME'];
        }
    }
    public function setDbUser($db_user=null) {
        if ($db_user) {
            $this->db_user = $db_user;
        }
    }
    public function setDbPassword($db_password=null) {
        if ($db_password) {
            $this->db_password = $db_password;
        }
    }
    public function setDbName($db_name=null) {
        if ($db_name) {
            $this->db_name = $db_name;
        }
    }
    public function setConn($conn) {
        if ($conn) {
            $this->conn = $conn;
        }
    }

    public function getDbServer() {
        return $this->db_server;
    }
    public function getDbUser() {
        return $this->db_user;
    }
    public function getDbPassword() {
        return $this->db_password;
    }
    public function getDbName() {
        return $this->db_name;
    }
    /**
     * getConn().
     * Gets a Connection
     * If it does no exist, the creates a database connection
     */
    public function getConn() {
        $this->connect();
        return $this->conn;
    }
    public function run($sql=null) {
        return ($this->getConn()->query($sql));
    }
    public function runMulti($sql=null) {
        return ($this->getConn()->multi_query($sql));
    }
    public function getError() {
        return $this->getConn()->error;
    }

    /**
     * connect().
     * Creates a database connection using MySQLi extension
     * PDO will work on 12 different database systems, whereas MySQLi will only work with MySQL databases.
     */

    public function connect() {
        $db_server   = $this->getDbServer();
        $db_user     = $this->getDbUser();
        $db_password = $this->getDbPassword();
        $db_name     = $this->getDbName();
        // echo("connect to: ".$dbServer.",".$dbUser.",".$dbPassword.",".$dbName."<br/>");
        $this->setConn(new mysqli($db_server, $db_user, $db_password, $db_name));

        // Check connection
        if ($this->conn->connect_error) {
            die("Database: Failed to connect to MySQL: ".$this->conn->connect_error);
        }
    }

    public function commit() {
        $this->getConn()->commit();
    }

    public function close() {
        $this->getConn()->close();
        $this->setConn(null);
    }

}

?>