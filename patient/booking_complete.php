<?php
session_start();

// Redirect unauthenticated or incorrect user types
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit(); // Always exit after a header redirect
}

include "../config.php"; // Ensure config.php connects to the database
if (!isset($database) || !$database) {
    die("Database connection failed.");
}

// Fetch user details
$useremail = $_SESSION["user"];
// Use prepared statement for fetching user details to prevent potential issues
$stmt_user = $database->prepare("SELECT pid, pname FROM patient WHERE pemail = ?");
$stmt_user->bind_param("s", $useremail);
$stmt_user->execute();
$userrow = $stmt_user->get_result();

if ($userrow->num_rows === 0) {
    // Handle case where patient email doesn't exist (e.g., deleted account)
    header("location: ../login.php");
    exit();
}
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch['pid'];
$username = $userfetch['pname'];
$stmt_user->close(); // Close the statement

// Handle POST request from booking.php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["booknow"])) {
    // Sanitize and validate inputs
    $scheduleid = (int)$_POST["scheduleid"];
    $selectedSlotTime = $_POST["selected_slot_time"]; // e.g., "07:30:00"
    // $bookingDate = $_POST["date"]; // Removed: This was $today from booking.php, the current date.
    // We will now use the actual scheduled date combined with selected time.

    // 1. Get schedule details including scheduledate, scheduletime, nop, and estimated_duration
    $stmt_schedule_details = $database->prepare("SELECT scheduledate, scheduletime, nop, estimated_duration FROM schedule WHERE scheduleid = ?");
    $stmt_schedule_details->bind_param("i", $scheduleid);
    $stmt_schedule_details->execute();
    $result_schedule_details = $stmt_schedule_details->get_result();

    if ($result_schedule_details->num_rows === 0) {
        // Schedule not found, redirect with error
        header("location: appointment.php?action=schedule-not-found");
        exit();
    }
    $schedule_data = $result_schedule_details->fetch_assoc();
    $actualScheduledDate = $schedule_data['scheduledate']; // The date the doctor scheduled
    $overallScheduleStartTime = $schedule_data['scheduletime']; // The start time of the entire schedule
    $maxAllowedPatients = $schedule_data['nop'];

    // Convert estimated_duration to seconds for calculation, ensuring consistency
    $estimated_duration_str = $schedule_data['estimated_duration']; // Format 'HH:MM:SS'
    list($h, $m, $s) = explode(':', $estimated_duration_str);
    $slotDurationSeconds = ($h * 3600) + ($m * 60) + $s;

    $stmt_schedule_details->close();

    // Combine actual scheduled date with the selected slot time
    $fullAppointmentDateTime = $actualScheduledDate . ' ' . $selectedSlotTime; // e.g., "YYYY-MM-DD HH:MM:SS"

    // Optional: Start a transaction to ensure atomicity
    // This is good practice for critical updates
    $database->begin_transaction();
    try {
        // 2. Check for duplicate booking for this patient on this schedule
        // A patient should only be able to book one slot per schedule
        $stmt_duplicate_check = $database->prepare("SELECT appoid FROM appointment WHERE pid = ? AND scheduleid = ?");
        $stmt_duplicate_check->bind_param("ii", $userid, $scheduleid);
        $stmt_duplicate_check->execute();
        $duplicate_result = $stmt_duplicate_check->get_result();

        if ($duplicate_result->num_rows > 0) {
            // Patient already booked this appointment (any slot within it)
            $database->rollback(); // Rollback any potential transaction start
            header("location: appointment.php?action=already-booked");
            exit();
        }
        $stmt_duplicate_check->close();

        // 3. Determine the actual apponum (slot order) and check if the chosen slot is taken
        // This calculates which sequential slot number the chosen time corresponds to.
        $startTimeObj = new DateTime($overallScheduleStartTime);
        $selectedTimeObj = new DateTime($selectedSlotTime);
        $intervalSeconds = $selectedTimeObj->getTimestamp() - $startTimeObj->getTimestamp();

        // Calculate the apponum (slot index) based on the interval and duration
        // Add 1 because apponum starts from 1
        $calculatedAppoNum = ($slotDurationSeconds > 0) ? ($intervalSeconds / $slotDurationSeconds) + 1 : 1;


        // Ensure calculated apponum is within valid range (1 to maxAllowedPatients)
        if ($calculatedAppoNum < 1 || $calculatedAppoNum > $maxAllowedPatients) {
            $database->rollback();
            header("location: appointment.php?action=invalid-slot");
            exit();
        }

        // Now, check if this specific 'apponum' (slot order) is already taken for this schedule
        $stmt_slot_taken_check = $database->prepare("SELECT appoid FROM appointment WHERE scheduleid = ? AND apponum = ?");
        $stmt_slot_taken_check->bind_param("ii", $scheduleid, $calculatedAppoNum);
        $stmt_slot_taken_check->execute();
        $slot_taken_result = $stmt_slot_taken_check->get_result();

        if ($slot_taken_result->num_rows > 0) {
            // The specific slot number the patient tried to book is already taken
            $database->rollback();
            header("location: appointment.php?action=slot-taken");
            exit();
        }
        $stmt_slot_taken_check->close();

        // 4. If all checks pass, proceed with booking
        $stmt_insert_appointment = $database->prepare("INSERT INTO appointment(pid, apponum, scheduleid, appodate) VALUES (?, ?, ?, ?)");
        // Use 'iiis' for bind_param: integer, integer, integer, string (for DATETIME)
        $stmt_insert_appointment->bind_param("iiis", $userid, $calculatedAppoNum, $scheduleid, $fullAppointmentDateTime);

        if ($stmt_insert_appointment->execute()) {
            $database->commit(); // Commit transaction
            header("location: appointment.php?action=booking-added&id=" . $calculatedAppoNum); // id can be used to show slot number
            exit();
        } else {
            throw new Exception("Error inserting appointment: " . $stmt_insert_appointment->error);
        }
        $stmt_insert_appointment->close();
    } catch (Exception $e) {
        $database->rollback(); // Rollback on any error within the transaction
        error_log("Booking Error: " . $e->getMessage()); // Log the error for debugging
        header("location: appointment.php?action=booking-failed");
        exit();
    }
} else {
    // If not a POST request or 'booknow' not set, redirect to schedule page
    header("location: schedule.php");
    exit();
}
