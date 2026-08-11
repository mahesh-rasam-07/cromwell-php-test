<?php
$db_host = '127.0.0.1';
$db_port = '5432';
$db_name = 'cromwell_test';
$db_user = 'postgres';
$db_pass = 'your_postgres_password_here';

$conn_string = "host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass";
$db = pg_connect($conn_string);

if (!$db) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Could not connect to database']);
    exit;
}