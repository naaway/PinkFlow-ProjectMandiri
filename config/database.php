<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
 $conn=new mysqli('localhost','root','','pinkflow_projectmandiri');
 $conn->set_charset('utf8mb4');
} catch(mysqli_sql_exception $e) {
 http_response_code(500); exit('Database pinkflow_projectmandiri belum tersedia. Import database/pinkflow_projectmandiri.sql melalui phpMyAdmin.');
}
