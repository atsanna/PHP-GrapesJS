<?php

define('DBHOST', 'localhost');
define('DBUSER', 'root');
define('DBPASS', 'truelove');
define('DBNAME', 'newtestcms');
$conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

    /* If connection fails for some reason */
    if ($conn->connect_error) {
        die('Error, Database connection failed: (' . $conn->connect_errno . ') ' . $conn->connect_error);
    }
$conn->set_charset('utf8mb4');
$base = 'http://localhost:120/PHP-GrapesJS/';
$path = 'http://' . $_SERVER['SCRIPT_FILENAME'];
$fname = basename($path, '.php');
require 'function.php';
require 'define.php';

        
    ?>
    