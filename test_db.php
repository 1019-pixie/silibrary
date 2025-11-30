<?php
require_once 'vendor/autoload.php';

use Library\Config\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "✅ Database connection successful!\n";
    
    // Test query
    $result = $db->query("SELECT COUNT(*) as count FROM books");
    echo "✅ Found " . $result[0]['count'] . " books\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}