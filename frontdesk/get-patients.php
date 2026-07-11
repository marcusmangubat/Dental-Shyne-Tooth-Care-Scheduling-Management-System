<?php
require_once '../config.php';

header('Content-Type: application/json');



$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT pid, pname, pemail, ptel, pdob, paddress FROM patient";

if (!empty($search)) {
    $search = $database->real_escape_string($search);
    $sql .= " WHERE pname LIKE '%$search%' OR pemail LIKE '%$search%' OR ptel LIKE '%$search%'";
}

$sql .= " ORDER BY pid DESC";

$result = $database->query($sql);

$patients = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}

$database->close();

echo json_encode([
    'success' => true,
    'data' => $patients
]);
