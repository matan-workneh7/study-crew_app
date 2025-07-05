<?php
require_once 'config.php';

// Add availability column to assistants table
$sql = "ALTER TABLE assistants ADD COLUMN IF NOT EXISTS availability TEXT";

if ($conn->query($sql) === TRUE) {
    echo "Successfully added 'availability' column to 'assistants' table.\n";
    
    // Check if the column was actually added
    $result = $conn->query("SHOW COLUMNS FROM assistants LIKE 'availability'");
    if ($result->num_rows > 0) {
        echo "Verified: 'availability' column exists in 'assistants' table.\n";
    } else {
        echo "Warning: Failed to verify 'availability' column was added.\n";
    }
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

$conn->close();
?>
