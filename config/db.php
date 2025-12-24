<?php
require_once("config.php");

$connection = Connection::connectDB();
if (!$connection) {
    // die("❌ Connection failed. Check config.php or DB credentials.");
    exit;
}



?>