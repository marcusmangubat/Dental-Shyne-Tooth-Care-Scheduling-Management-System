<?php
include '../config.php';

header('Content-Type: application/json');
error_reporting(E_ALL); // Keep enabled for development, disable in production for security
ini_set('display_errors', 1); // Keep enabled for development, disable in production for security

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Input validation
if (empty($data['pname']) || empty($data['paddress']) || empty($data['pdob']) || empty($data['ptel']) || empty($data['ppassword']) || empty($data['pconfirm_password']) || empty($data['pgender'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}
if (!isset($data['pemail']) || !filter_var($data['pemail'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing email.']);
    exit;
}

$pemail = $data['pemail'];
$pname = $data['pname'];
$ppassword_raw = $data['ppassword'];
$pconfirm_password = $data['pconfirm_password'];
$paddress = $data['paddress'];
$pdob = $data['pdob'];
$ptel = $data['ptel'];
$pgender = $data['pgender']; // New: Get gender from input

// Password validation
if ($ppassword_raw !== $pconfirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

// Password validation regex: min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
$passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
if (!preg_match($passwordRegex, $ppassword_raw)) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.']);
    exit;
}

$ppassword = password_hash($ppassword_raw, PASSWORD_DEFAULT); // Hash the password after validation


// Check if email already exists
$checkEmail = $database->prepare("SELECT email FROM webuser WHERE email = ?");
if (!$checkEmail) {
    error_log("Prepare failed: " . $database->error, 3, '/path/to/error.log');
    echo json_encode(['success' => false, 'message' => 'Database error during email check (prepare).']);
    exit;
}
$checkEmail->bind_param("s", $pemail);
$checkEmail->execute();
$checkEmail->store_result(); // Store result to check num_rows
if ($checkEmail->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already exists.']);
    $checkEmail->close();
    $database->close();
    exit;
}
$checkEmail->close();

// Start transaction
$database->begin_transaction();

try {
    // Insert into webuser
    $stmt1 = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'p')");
    if (!$stmt1) {
        throw new Exception("Prepare statement 1 failed: " . $database->error);
    }
    $stmt1->bind_param("s", $pemail);
    $stmt1->execute();
    $stmt1->close();

    // Insert into patient (now includes pgender)
    $stmt2 = $database->prepare("INSERT INTO patient (pemail, pname, ppassword, paddress, pdob, ptel, pgender) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt2) {
        throw new Exception("Prepare statement 2 failed: " . $database->error);
    }
    $stmt2->bind_param("sssssss", $pemail, $pname, $ppassword, $paddress, $pdob, $ptel, $pgender); // Added 's' for pgender
    $stmt2->execute();
    $stmt2->close();

    $database->commit();
    echo json_encode(['success' => true, 'message' => 'Patient added successfully.']);
} catch (Exception $e) {
    $database->rollback();
    // Log the error securely in production
    error_log("Error adding patient: " . $e->getMessage() . " | SQL Error: " . $database->error, 3, '/path/to/error.log');
    echo json_encode(['success' => false, 'message' => 'Error adding patient: ' . $e->getMessage()]);
} finally {
    $database->close();
}
