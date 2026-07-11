<?php
require_once '../config.php';

header('Content-Type: application/json');


$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$date = $database->real_escape_string($date);

$sql = "SELECT s.scheduleid, s.title, s.scheduledate, s.scheduletime, s.nop, 
        d.docname, sp.sname as specialty,
        (SELECT COUNT(*) FROM appointment WHERE scheduleid = s.scheduleid) as booked
        FROM schedule s
        INNER JOIN doctor d ON s.docid = d.docid
        LEFT JOIN specialties sp ON d.specialties = sp.id
        WHERE s.scheduledate >= '$date'
        ORDER BY s.scheduledate, s.scheduletime";

$result = $database->query($sql);

$schedules = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['available'] = $row['nop'] - $row['booked'];
        $schedules[] = $row;
    }
}

$database->close();

echo json_encode([
    'success' => true,
    'data' => $schedules
]);
