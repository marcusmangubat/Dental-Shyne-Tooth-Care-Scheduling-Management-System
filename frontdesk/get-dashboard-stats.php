<?php
require_once '../config.php';

header('Content-Type: application/json');

// Get total patients
$totalPatients = $database->query("SELECT COUNT(*) as count FROM patient")->fetch_assoc()['count'];

// Get today's appointments
$today = date('Y-m-d');
$todayAppointments = $database->query("SELECT COUNT(*) as count FROM appointment a 
    INNER JOIN schedule s ON a.scheduleid = s.scheduleid 
    WHERE s.scheduledate = '$today'")->fetch_assoc()['count'];

// Get total doctors
$totalDoctors = $database->query("SELECT COUNT(*) as count FROM doctor")->fetch_assoc()['count'];

// Get pending appointments (upcoming)
$pendingAppointments = $database->query("SELECT COUNT(*) as count FROM appointment a 
    INNER JOIN schedule s ON a.scheduleid = s.scheduleid 
    WHERE s.scheduledate >= '$today'")->fetch_assoc()['count'];

$database->close();

echo json_encode([
    'success' => true,
    'data' => [
        'totalPatients' => $totalPatients,
        'todayAppointments' => $todayAppointments,
        'totalDoctors' => $totalDoctors,
        'pendingAppointments' => $pendingAppointments
    ]
]);
