<?php
include '../config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// input validation
if (empty($data['pid']) || !is_numeric($data['pid'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing patient ID.']);
    exit;
}
if (empty($data['pname']) || empty($data['paddress']) || empty($data['pdob']) || empty($data['ptel']) || empty($data['pgender'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}
if (!isset($data['pemail']) || !filter_var($data['pemail'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing email.']);
    exit;
}

$pid = $data['pid'];
$pemail = $data['pemail'];
$pname = $data['pname'];
$paddress = $data['paddress'];
$pdob = $data['pdob'];
$ptel = $data['ptel'];
$pgender = $data['pgender'];


// Check if email already exists for a *different* patient
try {
    $checkEmail = $database->prepare("SELECT pid FROM patient WHERE pemail = ? AND pid != ?");
    if (!$checkEmail) {
        throw new Exception("Prepare failed for email check: " . $database->error);
    }
    $checkEmail->bind_param("si", $pemail, $pid);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already used by another patient.']);
        $checkEmail->close();
        $database->close();
        exit;
    }
    $checkEmail->close();

    // Update patient data
    $stmt = $database->prepare("UPDATE patient SET pemail = ?, pname = ?, paddress = ?, pdob = ?, ptel = ?, pgender = ? WHERE pid = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed for update: " . $database->error);
    }
    $stmt->bind_param("ssssssi", $pemail, $pname, $paddress, $pdob, $ptel, $pgender, $pid);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Patient updated successfully.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Patient data unchanged or patient not found.']);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error updating patient: " . $e->getMessage() . " | SQL Error: " . $database->error, 3, '/path/to/error.log');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    $database->close();
}
