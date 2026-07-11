<?php
require_once '../config.php';

header('Content-Type: application/json');



$sql = "SELECT d.docid, d.docname, d.docemail, d.doctel, s.sname as specialty 
        FROM doctor d 
        LEFT JOIN specialties s ON d.specialties = s.id 
        ORDER BY d.docname";

$result = $database->query($sql);

$doctors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

$database->close();

echo json_encode([
    'success' => true,
    'data' => $doctors
]);
