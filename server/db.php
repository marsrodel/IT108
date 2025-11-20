<?php
// Simple MySQLi connection for XAMPP
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'covid_db';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
  die('Database connection failed: ' . mysqli_connect_error());
}
?>
