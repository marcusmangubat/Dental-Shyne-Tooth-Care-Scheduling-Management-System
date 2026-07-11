<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$pid = intval($data['pid']);
$scheduleid = intval($data['scheduleid']);



// Check if schedule is full
$schedule = $database->query("SELECT nop, 
    (SELECT COUNT(*) FROM appointment WHERE scheduleid = $scheduleid) as booked 
    FROM schedule WHERE scheduleid = $scheduleid")->fetch_assoc();

if ($schedule['booked'] >= $schedule['nop']) {
    echo json_encode(['success' => false, 'message' => 'Schedule is full']);
    $database->close();
    exit;
}

// Get next appointment number
$apponum = $schedule['booked'] + 1;

// Get schedule date
$scheduleDate = $database->query("SELECT scheduledate FROM schedule WHERE scheduleid = $scheduleid")->fetch_assoc()['scheduledate'];

// Insert appointment
$sql = "INSERT INTO appointment (pid, apponum, scheduleid, appodate) 
        VALUES ($pid, $apponum, $scheduleid, '$scheduleDate')";

if ($database->query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Appointment booked successfully', 'apponum' => $apponum]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error booking appointment']);
}

$database->close();
