<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$appoid = intval($data['appoid']);



$sql = "DELETE FROM appointment WHERE appoid = $appoid";

if ($database->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error cancelling appointment']);
}

$database->close();
