<?php
include '../config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid patient ID.']);
    exit;
}

$pid = $_GET['pid'];

try {
    $stmt = $database->prepare("SELECT pid, pemail, pname, paddress, pdob, ptel, pgender FROM patient WHERE pid = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $database->error);
    }
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $patientData = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $patientData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Patient not found.']);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error getting patient details: " . $e->getMessage(), 3, '/path/to/error.log');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    $database->close();
}
