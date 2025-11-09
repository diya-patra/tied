<?php
$env = parse_ini_file(__DIR__ . '/.env');
// Tied Forum DB Connect

$DB_SERVER = $env['DB_SERVER'];
$DB_USERNAME = $env['DB_USER'];
$DB_PASSWORD = $env['DB_PASS'];
$DB_NAME = $env['DB_NAME'];

$mysqli = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

if ($mysqli->connect_error) {
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}
?>