<?php
// database connection config. i changed it from pdo to mysqli because i was having issues with pdo and i wanted to use mysqli instead sorrrrrryyyyyyyyyyyy piro sadyang dele rajod nako sha madefend ni maam LOL
$host     = 'localhost';
$dbname   = 'beautyreserve'; // CHANGE THIS to the datbase name thanks john francis
$username = 'root';  // Default XAMPP username
$password = '';      // Default XAMPP password (leave empty)

// Report database failures as exceptions so callers can handle them.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $username, $password, $dbname);
$conn->set_charset('utf8mb4');
