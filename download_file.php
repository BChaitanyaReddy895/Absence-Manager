<?php
require 'db.php';

$name = $_GET['name'] ?? '';
$srn = $_GET['srn'] ?? '';

if ($name && $srn) {
    $sql = "SELECT Supporting_files FROM leave_form WHERE Name = ? AND SRN = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $srn);
    $stmt->execute();
    $stmt->bind_result($file);
    $stmt->fetch();
    $stmt->close();

    if ($file) {
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"supporting_file\"");
        echo $file;
    } else {
        echo "File not found.";
    }
} else {
    echo "Invalid request.";
}
$conn->close();
?>
