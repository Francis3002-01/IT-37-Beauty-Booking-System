<?php
$host       = 'localhost';
$dbname     = 'beautyreserve'; 
$db_user    = 'root';   // Changed from $username to avoid overwriting your form input!
$password   = '';       

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $db_user, $password, $dbname);
$conn->set_charset('utf8mb4');