<?php
require_once 'function.php';

$host       = "localhost";
$user       = "root";
$password   = "";
$database   = "db_zurich";
$koneksi    = mysqli_connect($host, $user, $password, $database);
try {   
        $pdo = new PDO("mysql:host=$host;dbname=$database", 
        $user, $password,
        array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ));
    }catch (PDOException $e) {
        die('database connection failed: ' . $e->getMessage());
    }

 
?>