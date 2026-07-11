<?php
require_once '../config.php';

header('Content-Type: application/json');



$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$date = $database->real_escape_string($date);

$sql = "SELECT a.appoid, a.apponum, a.appodate, 
        p.pname, p.ptel, p.pemail,
        d.docname, s.scheduletime, s.title,
        sp.sname as specialty
        FROM appointment a
        INNER JOIN patient p ON a.pid = p.pid
        INNER JOIN schedule s ON a.scheduleid = s.scheduleid
        INNER JOIN doctor d ON s.docid = d.docid
        LEFT JOIN specialties sp ON d.specialties = sp.id
        WHERE s.scheduledate = '$date'
        ORDER BY s.scheduletime, a.apponum";

$result = $database->query($sql);

$appointments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

$database->close();

echo json_encode([
    'success' => true,
    'data' => $appointments
]);
