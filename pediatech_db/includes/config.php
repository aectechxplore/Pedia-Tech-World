<?php
// Database Settings
$host = 'localhost';
$db_user = 'root';      // Your Database Username
$db_pass = '';          // Your Database Password
$db_name = 'pediatech_db';

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// --- STATIC URL CONFIGURATION ---
// CHANGE THIS to your actual domain or localhost path
// Example 1 (Localhost): "http://localhost/pediatech_db/"
// Example 2 (Live Site): "https://pediatechworld.com/"
$base_url = "http://localhost/pediatech_db/"; 

// Helper function to get the full URL
function base_url($path = '') {
    global $base_url;
    return $base_url . ltrim($path, '/');
}
?>