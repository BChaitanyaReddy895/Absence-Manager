<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require 'db.php';

// Check if table name is provided
if (!isset($_GET['table']) || empty($_GET['table'])) {
    die("Table name is required!");
}

$table = $_GET['table'];

// Validate table name
$allowedTables = ['leave_form', 'individual_event_form', 'team_event_form'];
if (!in_array($table, $allowedTables)) {
    die("Invalid table name!");
}

// Set headers for Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename={$table}.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Query the table data
$sql = "SELECT * FROM $table";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // Fetch field names
    $fields = $result->fetch_fields();

    // Output table headers
    echo "<table border='1'><tr>";
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field->name) . "</th>";
    }
    echo "</tr>";

    // Output rows
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No data available in the selected table.";
}

$conn->close();
?>
