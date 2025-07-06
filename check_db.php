<?php
require_once 'config.php';

try {
    // Test database connection
    $conn = getDbConnection();
    echo "Database connection successful!<br><br>";
    
    // Check if messages table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'messages'");
    if ($stmt->rowCount() > 0) {
        echo "Messages table exists.<br><br>";
        
        // Show table structure
        $stmt = $conn->query("DESCRIBE messages");
        echo "<h3>Messages Table Structure:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample data
        $stmt = $conn->query("SELECT * FROM messages LIMIT 5");
        echo "<h3>Sample Messages (first 5 records):</h3>";
        if ($stmt->rowCount() > 0) {
            echo "<table border='1'>";
            $first = true;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($first) {
                    echo "<tr>";
                    foreach (array_keys($row) as $key) {
                        echo "<th>" . htmlspecialchars($key) . "</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No messages found in the database.";
        }
    } else {
        echo "Messages table does not exist.";
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
