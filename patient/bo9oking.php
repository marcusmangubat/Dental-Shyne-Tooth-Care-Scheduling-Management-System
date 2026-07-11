<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
}

include "../config.php";
$useremail = $_SESSION["user"];
$userrow = $database->query("SELECT * FROM patient WHERE pemail = '$useremail'");
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch['pid'];

if (isset($_GET['scheduleid']) && isset($_GET['scheduledate'])) {
    $scheduleid = $_GET['scheduleid'];
    $scheduledate = $_GET['scheduledate'];

    // Check slot availability
    $sql = "SELECT COUNT(a.appoid) AS booked, s.nop 
            FROM schedule s 
            LEFT JOIN appointment a ON s.scheduleid = a.scheduleid 
            WHERE s.scheduleid = ? 
            GROUP BY s.scheduleid, s.nop";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $scheduleid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row['booked'] < $row['nop']) {
        // Generate apponum
        $sql = "SELECT COALESCE(MAX(apponum), 0) + 1 AS new_apponum 
                FROM appointment 
                WHERE scheduleid = ?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("i", $scheduleid);
        $stmt->execute();
        $apponum = $stmt->get_result()->fetch_assoc()['new_apponum'];

        // Book appointment
        $sql = "INSERT INTO appointment (pid, scheduleid, apponum, appodate) 
                VALUES (?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("iiis", $userid, $scheduleid, $apponum, $scheduledate);
        if ($stmt->execute()) {
            $_SESSION['confirmation'] = ['appoid' => $stmt->insert_id];
            header("Location: schedule.php");
        } else {
            $error = "Booking failed.";
        }
    } else {
        $error = "Slot no longer available.";
    }
    $stmt->close();
}

if (isset($error)) {
    echo "<script>alert('$error'); window.location='schedule.php?date=$scheduledate';</script>";
}
